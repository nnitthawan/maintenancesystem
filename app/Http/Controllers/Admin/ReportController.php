<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Department;
use App\Models\Ticket;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $baseQuery = Ticket::query();

        if ($request->filled('from')) {
            $baseQuery->whereDate('created_at', '>=', $request->date('from'));
        }

        if ($request->filled('to')) {
            $baseQuery->whereDate('created_at', '<=', $request->date('to'));
        }

        if ($request->filled('category_id')) {
            $baseQuery->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('department_id')) {
            $baseQuery->whereHas('reporter', function ($reporterQuery) use ($request) {
                $reporterQuery->where('department_id', $request->integer('department_id'));
            });
        }

        $summaryQuery = clone $baseQuery;
        $summary = [
            'total' => (clone $summaryQuery)->count(),
            'pending' => (clone $summaryQuery)->where('status', 'pending')->count(),
            'in_progress' => (clone $summaryQuery)->where('status', 'in_progress')->count(),
            'completed' => (clone $summaryQuery)->where('status', 'completed')->count(),
            'cancelled' => (clone $summaryQuery)->where('status', 'cancelled')->count(),
        ];

        $categoryTotals = (clone $summaryQuery)
            ->selectRaw('category_id, COUNT(*) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        $query = (clone $baseQuery)->with(['reporter.department', 'category']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $tickets = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::orderBy('category_name')->get();
        $departments = Department::orderBy('department_name')->get();
        $categorySummary = $categories
            ->map(fn ($category) => [
                'label' => $category->category_name,
                'total' => (int) ($categoryTotals[$category->id] ?? 0),
            ])
            ->filter(fn ($category) => $category['total'] > 0)
            ->sortByDesc('total')
            ->values();

        if ($categoryTotals->has(null) && $categoryTotals->get(null) > 0) {
            $categorySummary->push([
                'label' => 'ไม่ระบุหมวดหมู่',
                'total' => (int) $categoryTotals->get(null),
            ]);
        }

        return view('admin.reports.index', compact('tickets', 'summary', 'categories', 'departments', 'categorySummary'));
    }
}
