<?php

namespace App\Http\Controllers\Admin;

use App\Exports\TicketsExport;
use App\Http\Controllers\Controller;
use App\Imports\UsersImport;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class AdminUserController extends Controller
{
    /**
     * รายการผู้ใช้งานทั้งหมด
     */
    public function index(Request $request)
    {
        $query = User::with(['department', 'roleRelation'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $users = $query->paginate(6)->withQueryString();
        $roles = Role::all();
        $departments = Department::all();

        return view('admin.users.index', compact('users', 'roles', 'departments'));
    }

    /**
     * ฟอร์มสร้างผู้ใช้งานใหม่
     */
    public function create()
    {
        $roles = Role::all();
        $departments = Department::all();
        return view('admin.users.create', compact('roles', 'departments'));
    }

    /**
     * บันทึกผู้ใช้งานใหม่
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'username'      => 'required|string|max:100|unique:users,username',
            'phone'         => 'nullable|string|max:20',
            'department_id' => 'nullable|exists:departments,id',
            'role_id'       => 'required|exists:roles,id',
            'password'      => 'required|string|min:6|confirmed',
        ], [
            'name.required'          => 'กรุณากรอกชื่อ-นามสกุล',
            'username.required'      => 'กรุณากรอกชื่อผู้ใช้',
            'username.unique'        => 'ชื่อผู้ใช้นี้มีอยู่ในระบบแล้ว',
            'password.required'      => 'กรุณากรอกรหัสผ่าน',
            'password.min'           => 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร',
            'password.confirmed'     => 'รหัสผ่านยืนยันไม่ตรงกัน',
            'role_id.required'       => 'กรุณาเลือกบทบาท',
        ]);

        User::create([
            'name'          => $request->name,
            'username'      => $request->username,
            'phone'         => $request->phone ?? '',
            'department_id' => $request->department_id,
            'role_id'       => $request->role_id,
            'password'      => Hash::make($request->password),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'เพิ่มผู้ใช้งาน ' . $request->name . ' เรียบร้อยแล้ว');
    }

    /**
     * ฟอร์มแก้ไขผู้ใช้งาน
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();
        $departments = Department::all();
        return view('admin.users.edit', compact('user', 'roles', 'departments'));
    }

    /**
     * อัปเดตข้อมูลผู้ใช้งาน
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'          => 'required|string|max:255',
            'username'      => 'required|string|max:100|unique:users,username,' . $id,
            'phone'         => 'nullable|string|max:20',
            'department_id' => 'nullable|exists:departments,id',
            'role_id'       => 'required|exists:roles,id',
            'password'      => 'nullable|string|min:6|confirmed',
        ], [
            'name.required'      => 'กรุณากรอกชื่อ-นามสกุล',
            'username.required'  => 'กรุณากรอกชื่อผู้ใช้',
            'username.unique'    => 'ชื่อผู้ใช้นี้มีอยู่ในระบบแล้ว',
            'password.min'       => 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร',
            'password.confirmed' => 'รหัสผ่านยืนยันไม่ตรงกัน',
        ]);

        $user->name          = $request->name;
        $user->username      = $request->username;
        $user->phone         = $request->phone ?? '';
        $user->department_id = $request->department_id;
        $user->role_id       = $request->role_id;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', 'แก้ไขข้อมูลผู้ใช้งาน ' . $user->name . ' เรียบร้อยแล้ว');
    }

    /**
     * ลบผู้ใช้งาน
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'ไม่สามารถลบบัญชีของตัวเองได้');
        }

        if ($user->tickets()->exists()) {
            return back()->with('error', 'ไม่สามารถลบบัญชีนี้ได้ เนื่องจากมีประวัติการแจ้งซ่อมอยู่');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'ลบผู้ใช้งาน ' . $name . ' เรียบร้อยแล้ว');
    }

    /**
     * Import ผู้ใช้งานจาก Excel
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ], [
            'excel_file.required' => 'กรุณาเลือกไฟล์ Excel',
            'excel_file.mimes'    => 'ไฟล์ต้องเป็นสกุล .xlsx, .xls หรือ .csv เท่านั้น',
            'excel_file.max'      => 'ขนาดไฟล์ต้องไม่เกิน 5 MB',
        ]);

        $import = new UsersImport();
        Excel::import($import, $request->file('excel_file'));

        $msg = "นำเข้าข้อมูลสำเร็จ {$import->imported} รายการ";
        if ($import->skipped > 0) {
            $msg .= " (ข้ามไป {$import->skipped} รายการ: " . implode(', ', array_slice($import->errors, 0, 3));
            if (count($import->errors) > 3) {
                $msg .= ' และอื่นๆ';
            }
            $msg .= ')';
        }

        return redirect()->route('admin.users.index')->with('success', $msg);
    }

    /**
     * Download Template Excel สำหรับ Import
     */
    public function downloadTemplate()
    {
        return Excel::download(new class implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\ShouldAutoSize {
            public function headings(): array
            {
                return ['username', 'name', 'phone', 'department_id', 'role_id', 'password'];
            }
            public function array(): array
            {
                return [
                    ['user001', 'นายตัวอย่าง ใจดี', '0812345678', 3, 3, 'password123'],
                ];
            }
        }, 'template_import_users.xlsx');
    }

    /**
     * Export รายการแจ้งซ่อมเป็น Excel
     */
    public function exportTickets(Request $request)
    {
        $filters = $request->validate([
            'status' => 'nullable|in:pending,in_progress,completed,cancelled',
            'category_id' => 'nullable|integer|exists:categories,id',
            'department_id' => 'nullable|integer|exists:departments,id',
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
        ]);

        $filename = 'tickets_export_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new TicketsExport(
            $filters['status'] ?? '',
            $filters['category_id'] ?? null,
            $filters['department_id'] ?? null,
            $filters['from'] ?? null,
            $filters['to'] ?? null,
        ), $filename);
    }
}
