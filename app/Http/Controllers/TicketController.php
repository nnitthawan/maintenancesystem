<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Notification;
use App\Models\Ticket;
use App\Models\TicketImage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    /**
     * Display a listing of tickets.
     */
    public function index(Request $request)
    {
        $query = Ticket::with(['reporter.department', 'category', 'images'])
            ->latest();

        // If regular user, show only their own tickets, if admin show all
        if (Auth::user()->role !== 'admin') {
            $query->where('user_id', Auth::id());
        }

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Search by ticket_no, symptom, asset_no, location
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_no', 'like', "%{$search}%")
                  ->orWhere('asset_no', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('symptom', 'like', "%{$search}%");
            });
        }

        $tickets = $query->paginate(6)->withQueryString();
        $categories = Category::all();

        return view('tickets.index', compact('tickets', 'categories'));
    }

    /**
     * Show the form for creating a new ticket.
     */
    public function create()
    {
        $categories = Category::all();
        return view('tickets.create', compact('categories'));
    }

    /**
     * Store a newly created ticket in storage.
     */
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

        // Generate Ticket No (e.g. TK-20260911-0001)
        $todayStr = date('Ymd');
        $countToday = Ticket::whereDate('created_at', now()->toDateString())->count() + 1;
        $ticketNo = 'TK-' . $todayStr . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);

        $ticket = Ticket::create([
            'ticket_no' => $ticketNo,
            'user_id' => Auth::id(),
            'category_id' => $request->category_id,
            'asset_no' => $request->asset_no,
            'location' => $request->location,
            'symptom' => $request->symptom,
            'status' => 'pending',
        ]);

        // Handle uploaded images
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

        User::all()
            ->filter(fn (User $user) => $user->isAdmin())
            ->each(fn (User $admin) => Notification::create([
                'user_id' => $admin->id,
                'ticket_id' => $ticket->id,
                'message' => 'มีรายการแจ้งซ่อมใหม่ ' . $ticket->ticket_no . ' จาก ' . Auth::user()->name,
            ]));

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'ส่งข้อมูลแจ้งซ่อมเรียบร้อยแล้ว รหัสรายการ: ' . $ticketNo);
    }

    public function show($id)
    {
        $ticket = Ticket::with(['reporter.department', 'category', 'images'])->findOrFail($id);

        if (Auth::user()->role !== 'admin' && $ticket->user_id !== Auth::id()) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงรายการนี้');
        }

        return view('tickets.show', compact('ticket'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled',
        ]);

        $ticket = Ticket::findOrFail($id);
        $oldStatus = $ticket->status;
        $ticket->status = $request->status;
        $ticket->save();

        if ($oldStatus !== $ticket->status) {
            Notification::create([
                'user_id' => $ticket->user_id,
                'ticket_id' => $ticket->id,
                'message' => 'รายการ ' . $ticket->ticket_no . ' เปลี่ยนสถานะเป็น ' . $ticket->status,
            ]);
        }

        return redirect()->back()->with('success', 'อัปเดตสถานะการแจ้งซ่อมสำเร็จ');
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
            ->filter(fn (User $user) => $user->isAdmin())
            ->each(fn (User $admin) => Notification::create([
                'user_id' => $admin->id,
                'ticket_id' => $ticket->id,
                'message' => 'ผู้แจ้งยกเลิกรายการแจ้งซ่อม ' . $ticket->ticket_no,
            ]));

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'ยกเลิกรายการแจ้งซ่อมเรียบร้อยแล้ว');
    }
}
