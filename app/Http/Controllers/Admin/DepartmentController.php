<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount('users')
            ->orderBy('id')
            ->paginate(10);

        return view('admin.departments.index', compact('departments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'department_name' => 'required|string|max:150|unique:departments,department_name',
        ], [
            'department_name.required' => 'กรุณาระบุชื่อกลุ่มงาน / แผนก',
            'department_name.unique' => 'มีชื่อกลุ่มงานนี้อยู่ในระบบแล้ว',
        ]);

        Department::create($data);

        return back()->with('success', 'เพิ่มกลุ่มงานเรียบร้อยแล้ว');
    }

    public function update(Request $request, $id)
    {
        $department = Department::findOrFail($id);

        $data = $request->validate([
            'department_name' => 'required|string|max:150|unique:departments,department_name,' . $department->id,
        ], [
            'department_name.required' => 'กรุณาระบุชื่อกลุ่มงาน / แผนก',
            'department_name.unique' => 'มีชื่อกลุ่มงานนี้อยู่ในระบบแล้ว',
        ]);

        $department->update($data);

        return back()->with('success', 'แก้ไขกลุ่มงานเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $department = Department::findOrFail($id);
        $userCount = $department->users()->count();

        if ($userCount > 0) {
            return back()->with('error', "ไม่สามารถลบได้ เนื่องจากมีผู้ใช้งานสังกัดอยู่ในกลุ่มงานนี้ {$userCount} คน");
        }

        $department->delete();

        return back()->with('success', 'ลบกลุ่มงานเรียบร้อยแล้ว');
    }
}