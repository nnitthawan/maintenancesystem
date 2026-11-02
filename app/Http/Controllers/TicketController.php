<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Notification;
use App\Models\Ticket;
use App\Models\TicketImage;
use App\Models\User;
use App\Services\LineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    protected LineService $lineService;

    // Inject LineService เข้ามาผ่าน Constructor
    public function __construct(LineService $lineService)
    {
        $this->lineService = $lineService;
    }

    /**
     * Display a listing of tickets.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Ticket::with(['reporter.department', 'assignedStaff', 'category', 'images'])
            ->latest();

        if ($user->role === 'staff') {
            $query->where(function ($ticketQuery) use ($user) {
                $ticketQuery->where('assigned_staff_id', $user->id)
                    ->orWhere(function ($unassignedQuery) {
                        $unassignedQuery->whereNull('assigned_staff_id')
                            ->where('status', 'pending');
                    });
            });
        } elseif ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        $stats = [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
            'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
        ];

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Search by ticket details, category, or reporter name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_no', 'like', "%{$search}%")
                    ->orWhere('asset_no', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('symptom', 'like', "%{$search}%")
                    ->orWhereHas('reporter', function ($reporterQuery) use ($search) {
                        $reporterQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('category', function ($categoryQuery) use ($search) {
                        $categoryQuery->where('category_name', 'like', "%{$search}%");
                    });
            });
        }

        $tickets = $query->paginate(6)->withQueryString();
        $categories = Category::all();

        return view('tickets.index', compact('tickets', 'categories', 'stats'));
    }

    // สร้างการแจ้งซ่อมใหม่
    public function create()
    {
        $categories = Category::all();

        return view('tickets.create', compact('categories'));
    }

    // ตัวตรวจสอบข้อมูลที่กรอกว่าครบถ้วนตามเงื่อนไขไหม
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'asset_no' => 'nullable|string|max:100',
            'location' => 'required|string|max:255',
            'symptom' => 'required|string',
            'images' => 'nullable|array|max:5',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ], [
            'category_id.required' => 'กรุณาเลือกหมวดหมู่การแจ้งซ่อม',
            'location.required' => 'กรุณาระบุสถานที่/อาคาร/ห้อง',
            'symptom.required' => 'กรุณาระบุรายละเอียดปัญหา/อาการเสีย',
            'images.max' => 'แนบรูปภาพได้สูงสุด 5 รูป',
            'images.*.image' => 'ไฟล์แนบต้องเป็นรูปภาพเท่านั้น',
            'images.*.max' => 'ขนาดรูปภาพต้องไม่เกิน 5 MB',
        ]);

        // ตัวสร้างรหัสการซ่อม Ticket No (e.g. TK-20260911-0001)
        $todayStr = date('Ymd');
        $countToday = Ticket::whereDate('created_at', now()->toDateString())->count() + 1;
        $ticketNo = 'TK-'.$todayStr.'-'.str_pad($countToday, 4, '0', STR_PAD_LEFT);

        $ticket = Ticket::create([
            'ticket_no' => $ticketNo,
            'user_id' => Auth::id(),
            'category_id' => $request->category_id,
            'asset_no' => $request->asset_no,
            'location' => $request->location,
            'symptom' => $request->symptom,
            'status' => 'pending',
        ]);

        // ส่วนการอัปโหลด images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $path = $file->store('tickets', 'public');
                    TicketImage::create([
                        'ticket_id' => $ticket->id,
                        'image_path' => $path,
                    ]);
                }
            }
        }

        // 1. บันทึก Notification ในระบบเดิม
        User::all()
            ->filter(fn (User $user) => $user->role === 'admin')
            ->each(fn (User $admin) => Notification::create([
                'user_id' => $admin->id,
                'ticket_id' => $ticket->id,
                'message' => 'มีรายการแจ้งซ่อมใหม่ '.$ticket->ticket_no.' จาก '.Auth::user()->name,
            ]));

        // 2. ส่ง LINE แจ้งเตือนแอดมิน/กลุ่มไลน์ไอที เฉพาะเมื่อผู้แจ้งไม่ใช่แอดมิน
        $adminLineId = config('services.line.admin_id');
        if (Auth::user()->role !== 'admin' && ! empty($adminLineId)) {
            $ticket->load('reporter.department');
            $reporterName = $ticket->reporter->name ?? 'ไม่ระบุชื่อ';
            $reporterDepartment = $ticket->reporter->department->department_name ?? 'ไม่ระบุหน่วยงาน';
            $msgToAdmin = "🚨 **มีรายการแจ้งซ่อมใหม่**\n"
                        ."รหัส: {$ticket->ticket_no}\n"
                        ."ผู้แจ้ง: {$reporterName}\n"
                        ."หน่วยงาน: {$reporterDepartment}\n"
                        ."สถานที่: {$ticket->location}\n"
                        .'รหัสอุปกรณ์: '.($ticket->asset_no ?? '-')."\n"
                        ."อาการเสีย: {$ticket->symptom}\n"
                        .'รูปภาพแนบ: '.($ticket->images->isNotEmpty() ? 'มี' : 'ไม่มี')."\n"
                        .'ลิงก์จัดการที่ระบบ: '.route('tickets.show', $ticket->id);

            $this->lineService->sendPush($adminLineId, $msgToAdmin);
        }

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'ส่งข้อมูลแจ้งซ่อมเรียบร้อยแล้ว รหัสรายการ: '.$ticketNo);
    }

    public function show($id)
    {
        $ticket = Ticket::with(['reporter.department', 'assignedStaff', 'category', 'images'])->findOrFail($id);
        $user = Auth::user();

        $canView = $user->role === 'admin'
            || $ticket->user_id === $user->id
            || ($user->role === 'staff' && (
                $ticket->assigned_staff_id === $user->id
                || ($ticket->assigned_staff_id === null && $ticket->status === 'pending')
            ));

        if (! $canView) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงรายการนี้');
        }

        $staffUsers = $user->role === 'admin'
            ? User::whereHas('roleRelation', fn ($query) => $query->where('name', 'staff'))->orderBy('name')->get()
            : collect();

        return view('tickets.show', compact('ticket', 'staffUsers'));
    }

    public function updateStatus(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        $user = Auth::user();
        $isAdmin = $user->role === 'admin';
        $isAssignedStaff = $user->role === 'staff' && $ticket->assigned_staff_id === $user->id;

        abort_unless($isAdmin || $isAssignedStaff, 403);

        $allowedStatuses = $isAdmin
            ? ['pending', 'in_progress', 'completed', 'cancelled']
            : match ($ticket->status) {
                'pending' => ['in_progress'],
                'in_progress' => ['in_progress', 'completed'],
                default => [$ticket->status],
            };

        if ($request->input('repair_method') === 'external' && $ticket->repair_method !== 'external') {
            $request->merge(['status' => 'in_progress']);
        }

        $effectiveRepairMethod = $request->input('repair_method', $ticket->repair_method);
        $rules = [
            'status' => ['required', Rule::in($allowedStatuses)],
            'repair_method' => ['nullable', Rule::in(['in_house', 'external', 'unrepairable'])],
            'external_repair_location' => [
                'nullable',
                Rule::requiredIf($effectiveRepairMethod === 'external'),
                'string',
                'max:255',
            ],
            'external_repair_returned_at' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],
        ];

        if ($effectiveRepairMethod === 'external' && $request->input('status') === 'completed') {
            $rules['external_repair_returned_at'][] = 'required';
        }

        if ($isAdmin) {
            $rules['assigned_staff_id'] = [
                'nullable',
                Rule::requiredIf($effectiveRepairMethod === 'external'),
                'exists:users,id',
            ];
        }

        $request->validate($rules, [
            'repair_method.in' => 'กรุณาเลือกแนวทางการซ่อมที่ถูกต้อง',
            'external_repair_location.required' => 'กรุณาระบุร้านหรือสถานที่ที่ส่งซ่อม',
            'external_repair_returned_at.required' => 'กรุณาระบุวันที่ได้รับของคืนก่อนปิดงานซ่อมภายนอก',
            'external_repair_returned_at.before_or_equal' => 'วันที่ได้รับของคืนต้องไม่เป็นวันในอนาคต',
            'assigned_staff_id.required' => 'กรุณาเลือกเจ้าหน้าที่ภายในเพื่อประสานงานส่งซ่อม',
            'assigned_staff_id.exists' => 'เจ้าหน้าที่ที่เลือกไม่ถูกต้อง',
        ]);

        $newAssignedStaff = null;
        if ($isAdmin && $request->filled('assigned_staff_id')) {
            $newAssignedStaff = User::findOrFail($request->input('assigned_staff_id'));
            if ($newAssignedStaff->role !== 'staff') {
                return redirect()->back()
                    ->withErrors(['assigned_staff_id' => 'เลือกได้เฉพาะบัญชีเจ้าหน้าที่'])
                    ->withInput();
            }
        }

        if ($ticket->status === 'completed' && $request->status !== 'completed') {
            return redirect()->back()->with(
                'error',
                'รายการนี้ซ่อมเสร็จสิ้นแล้ว ไม่สามารถเปลี่ยนสถานะอื่นได้'
            );
        }

        $oldStatus = $ticket->status;
        $ticket->status = $request->status;
        if ($request->exists('repair_method') && ($isAdmin || in_array($ticket->status, ['pending', 'in_progress'], true))) {
            $ticket->repair_method = $request->input('repair_method') ?: null;
        }
        if ($effectiveRepairMethod === 'external') {
            $ticket->external_repair_location = $request->input(
                'external_repair_location',
                $ticket->external_repair_location
            );
            if ($request->exists('external_repair_returned_at')) {
                $ticket->external_repair_returned_at = $request->input('external_repair_returned_at') ?: null;
            }
        }
        $previousStaffId = $ticket->assigned_staff_id;
        if ($newAssignedStaff) {
            $ticket->assigned_staff_id = $newAssignedStaff->id;
        }
        $ticket->save();

        if ($newAssignedStaff && $previousStaffId !== $newAssignedStaff->id) {
            Notification::create([
                'user_id' => $newAssignedStaff->id,
                'ticket_id' => $ticket->id,
                'message' => 'คุณได้รับมอบหมายงานแจ้งซ่อม '.$ticket->ticket_no,
            ]);
        }

        if ($oldStatus !== $ticket->status) {
            $statusLabel = match ($ticket->status) {
                'pending' => 'รอรับเรื่อง',
                'in_progress' => 'กำลังดำเนินการ',
                'completed' => 'เสร็จสิ้น',
                'cancelled' => 'ยกเลิก',
                default => $ticket->status,
            };

            // 1. บันทึก Notification ในระบบเดิม
            Notification::create([
                'user_id' => $ticket->user_id,
                'ticket_id' => $ticket->id,
                'message' => 'รายการ '.$ticket->ticket_no.' เปลี่ยนสถานะเป็น '.$statusLabel,
            ]);

            // 2. ส่ง LINE แจ้งเตือนผู้แจ้งซ่อม (ถ้ามี line_user_id ในตาราง User) กำลังพัฒนา
            $reporter = $ticket->reporter;
            if ($reporter && ! empty($reporter->line_user_id)) {
                $emoji = match ($ticket->status) {
                    'pending' => '📌',
                    'in_progress' => '⚙️',
                    'completed' => '✅',
                    'cancelled' => '❌',
                    default => 'ℹ️',
                };

                $msgToUser = "{$emoji} **อัปเดตสถานะการแจ้งซ่อม**\n"
                           ."รหัสรายการ: {$ticket->ticket_no}\n"
                           ."สถานที่: {$ticket->location}\n"
                           ."อาการเสีย: {$ticket->symptom}\n"
                           ."-------------------\n"
                           ."สถานะล่าสุด: **{$statusLabel}**";

                $this->lineService->sendPush($reporter->line_user_id, $msgToUser);
            }
        }

        return redirect()->back()->with('success', 'อัปเดตสถานะการแจ้งซ่อมสำเร็จ');
    }

    public function assignStaff(Request $request, $id)
    {
        $request->validate([
            'assigned_staff_id' => 'nullable|exists:users,id',
        ]);

        $ticket = Ticket::findOrFail($id);
        $staffId = $request->input('assigned_staff_id');
        $staff = $staffId ? User::findOrFail($staffId) : null;

        if ($staff && $staff->role !== 'staff') {
            return redirect()->back()
                ->withErrors(['assigned_staff_id' => 'เลือกได้เฉพาะบัญชีที่มีบทบาท staff'])
                ->withInput();
        }

        $previousStaffId = $ticket->assigned_staff_id;
        $ticket->assigned_staff_id = $staff?->id;
        $ticket->save();

        if ($staff && $previousStaffId !== $staff->id) {
            Notification::create([
                'user_id' => $staff->id,
                'ticket_id' => $ticket->id,
                'message' => 'คุณได้รับมอบหมายงานแจ้งซ่อม '.$ticket->ticket_no,
            ]);
        }

        return redirect()->back()->with('success', 'มอบหมายผู้ดำเนินการเรียบร้อยแล้ว');
    }

    public function claim(Request $request, $id)
    {
        $user = Auth::user();
        abort_unless($user->role === 'staff', 403);

        $claimed = Ticket::whereKey($id)
            ->whereNull('assigned_staff_id')
            ->where('status', 'pending')
            ->update(['assigned_staff_id' => $user->id]);

        if (! $claimed) {
            return redirect()->back()->with('error', 'รายการนี้มีผู้รับผิดชอบแล้ว หรือไม่อยู่ในสถานะที่รับงานได้');
        }

        $ticket = Ticket::findOrFail($id);
        Notification::create([
            'user_id' => $ticket->user_id,
            'ticket_id' => $ticket->id,
            'message' => 'เจ้าหน้าที่ '.$user->name.' รับผิดชอบรายการแจ้งซ่อม '.$ticket->ticket_no,
        ]);

        return redirect()->back()->with('success', 'รับผิดชอบรายการแจ้งซ่อมเรียบร้อยแล้ว');
    }

    public function cancel(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        abort_unless($ticket->user_id === Auth::id(), 403);

        if ($ticket->status !== 'pending') {
            return redirect()->back()->with('error', 'ไม่สามารถยกเลิกรายการที่เจ้าหน้าที่เริ่มดำเนินการแล้วได้');
        }

        $ticket->update(['status' => 'cancelled']);

        User::all()
            ->filter(fn (User $user) => $user->role === 'admin')
            ->each(fn (User $admin) => Notification::create([
                'user_id' => $admin->id,
                'ticket_id' => $ticket->id,
                'message' => 'ผู้แจ้งยกเลิกรายการแจ้งซ่อม '.$ticket->ticket_no,
            ]));

        //ส่ง LINE บอกแอดมินเมื่อผู้แจ้งกดยกเลิก
        $adminLineId = config('services.line.admin_id');
        if (! empty($adminLineId)) {
            $msgCancel = "**ผู้แจ้งยกเลิกรายการแจ้งซ่อม**\n"
                       ."รหัสรายการ: {$ticket->ticket_no}\n"
                       .'ผู้แจ้ง: '.(Auth::user()->name ?? 'ไม่ระบุชื่อ');

            $this->lineService->sendPush($adminLineId, $msgCancel);
        }

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'ยกเลิกรายการแจ้งซ่อมเรียบร้อยแล้ว');
    }
}