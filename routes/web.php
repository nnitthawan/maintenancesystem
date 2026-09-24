<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\CategoriesController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use App\Models\Category;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Services\LineService;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $user = Auth::user();

    $query = Ticket::with(['reporter.department', 'category']);
    if ($user->role !== 'admin') {
        $query->where('user_id', $user->id);
    }

    $tickets = (clone $query)->latest()->take(5)->get();

    $stats = [
        'total' => (clone $query)->count(),
        'pending' => (clone $query)->where('status', 'pending')->count(),
        'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
        'completed' => (clone $query)->where('status', 'completed')->count(),
        'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
    ];

    $categorySummary = (clone $query)
        ->selectRaw('category_id, COUNT(*) as total')
        ->groupBy('category_id')
        ->with('category:id,category_name')
        ->get()
        ->map(fn (Ticket $ticket) => [
            'label' => $ticket->category?->category_name ?? 'ทั่วไป',
            'total' => (int) $ticket->total,
        ]);

    $statusLabels = [
        'pending' => 'รอรับเรื่อง',
        'in_progress' => 'กำลังดำเนินการ',
        'completed' => 'เสร็จสิ้น',
        'cancelled' => 'ยกเลิก',
    ];
    $statusSummary = collect($statusLabels)->map(fn (string $label, string $status) => [
        'label' => $label,
        'total' => $stats[$status],
    ])->values();

    $categories = Category::withCount([
        'tickets' => function ($ticketQuery) use ($user) {
            if ($user->role !== 'admin') {
                $ticketQuery->where('user_id', $user->id);
            }
        },
    ])->get();

    $view = $user->role === 'admin' ? 'admin.dashboard' : 'user.dashboard';

    return view($view, compact('tickets', 'stats', 'categorySummary', 'statusSummary', 'categories'));
})->middleware(['auth'])->name('dashboard');
// })->middleware(['auth', 'two-factor.setup'])->name('dashboard');

// Route::middleware(['auth', 'two-factor.setup'])->group(function () {
Route::middleware(['auth'])->group(function () {
    Route::get('/test-line', function (LineService $lineService) {
        $adminId = config('services.line.admin_id');
        $token = config('services.line.token');

        if (empty($token)) {
            return "Error: ไม่พบ LINE_MESSAGING_API_TOKEN ใน .env";
        }

        if (empty($adminId)) {
            return "Error: ไม่พบ IT_ADMIN_LINE_ID ใน .env";
        }

        $success = $lineService->sendPush($adminId, "ทดสอบการยิง LINE จากโปรเจกต์ Laravel บน Localhost สำเร็จ!");

        if ($success) {
            return "ส่งข้อความเข้า LINE เรียบร้อยแล้ว! เช็คข้อความในมือถือได้เลยครับ";
        }

        return "ส่งไม่ผ่าน! โปรดตรวจสอบข้อความ Error ที่ไฟล์ storage/logs/laravel.log";
    });
    // Tickets management
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{id}', [TicketController::class, 'show'])->name('tickets.show');
    Route::patch('/tickets/{id}/cancel', [TicketController::class, 'cancel'])->name('tickets.cancel');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // User Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/two-factor/enable', [TwoFactorController::class, 'enable'])->name('two-factor.enable');
    Route::post('/profile/two-factor/confirm', [TwoFactorController::class, 'confirm'])->name('two-factor.confirm');
    Route::delete('/profile/two-factor', [TwoFactorController::class, 'disable'])->name('two-factor.disable');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin routes
    Route::middleware('admin')->group(function () {
        Route::resource('admin/categories', CategoriesController::class)->names('admin.categories');

        Route::resource('admin/departments', DepartmentController::class)->names('admin.departments');

        Route::patch('/tickets/{id}/status', [TicketController::class, 'updateStatus'])->name('tickets.update-status');

        Route::get('admin/reports', [ReportController::class, 'index'])->name('admin.reports.index');
        Route::get('admin/users/export-tickets', [AdminUserController::class, 'exportTickets'])->name('admin.users.export-tickets');
        Route::get('admin/users/template', [AdminUserController::class, 'downloadTemplate'])->name('admin.users.template');
        Route::post('admin/users/import', [AdminUserController::class, 'importExcel'])->name('admin.users.import');
        Route::resource('admin/users', AdminUserController::class)->names('admin.users');
    });
});

require __DIR__.'/auth.php';
