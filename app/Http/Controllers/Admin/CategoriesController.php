<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('tickets')->orderBy('id')->paginate(10);

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_name' => ['required', 'string', 'max:255', 'unique:categories,category_name'],
        ]);

        Category::create($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'เพิ่มประเภทงานซ่อมเรียบร้อยแล้ว');
    }

    public function show(Category $category)
    {
        //
    }

    public function edit(Category $category)
    {
        //
    }

    // แก้ไขประเภทการซ่อม
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'category_name' => ['required', 'string', 'max:255', 'unique:categories,category_name,'.$category->id],
        ]);

        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'แก้ไขประเภทงานซ่อมเรียบร้อยแล้ว');
    }

    // ลบรายการประเภทการซ่อม
    public function destroy(Category $category)
    {
        if ($category->tickets()->exists()) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'ไม่สามารถลบประเภทที่มีรายการแจ้งซ่อมอยู่ได้');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'ลบประเภทงานซ่อมเรียบร้อยแล้ว');
    }
}
