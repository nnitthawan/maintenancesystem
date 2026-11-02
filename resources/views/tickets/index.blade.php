<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">
            <span>
                {{ Auth::user()->isAdmin() ? 'รายการแจ้งซ่อมทั้งหมด' : (Auth::user()->role === 'staff' ? 'งานซ่อมสำหรับเจ้าหน้าที่' : 'รายการแจ้งซ่อมของฉัน') }}
            </span>
        </h2>
    </x-slot>

    <div class="data-page-stack">
        <!-- Search & Filter Controls -->
        <div class="filter-panel">
            <form method="GET" action="{{ route('tickets.index') }}" class="filter-form ticket-filter-form">

                <!-- Search Box -->
                <div class="search-field">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="ค้นหาเลขที่, ชื่อผู้แจ้ง, ครุภัณฑ์, สถานที่ หรืออาการเสีย..."
                        class="filter-input search-input">
                    <i class="fa-solid fa-magnifying-glass search-field-icon" aria-hidden="true"></i>
                </div>

                <!-- Category Filter -->
                <div class="category-filter-field">
                    <select name="category_id" class="filter-select">
                        <option value="">-- ทุกหมวดหมู่ --</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}"
                                {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->category_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Action Button -->
                <div class="filter-actions">
                    <button type="submit" class="primary-action">
                        <i class="fa-solid fa-filter text-xs"></i>
                        <span>กรอง</span>
                    </button>
                    @if (request()->hasAny(['search', 'status', 'category_id']))
                        <a href="{{ route('tickets.index') }}" class="secondary-action" title="ล้างตัวกรอง">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </form>

            <!-- Status Tabs -->
            <div class="status-tabs">
                <a href="{{ route('tickets.index', array_merge(request()->query(), ['status' => ''])) }}"
                    class="status-tab {{ !request('status') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600' }}">
                    ทั้งหมด
                </a>
                <a href="{{ route('tickets.index', array_merge(request()->query(), ['status' => 'pending'])) }}"
                    class="status-tab {{ request('status') === 'pending' ? 'bg-amber-500 text-white' : 'bg-amber-50 text-amber-700' }}">
                    <i class="fa-solid fa-clock me-1"></i> รอรับเรื่อง
                </a>
                <a href="{{ route('tickets.index', array_merge(request()->query(), ['status' => 'in_progress'])) }}"
                    class="status-tab {{ request('status') === 'in_progress' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-700' }}">
                    <i class="fa-solid fa-screwdriver-wrench me-1"></i> กำลังดำเนินการ
                </a>
                <a href="{{ route('tickets.index', array_merge(request()->query(), ['status' => 'completed'])) }}"
                    class="status-tab {{ request('status') === 'completed' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700' }}">
                    <i class="fa-solid fa-check-circle me-1"></i> เสร็จสิ้น
                </a>
                <a href="{{ route('tickets.index', array_merge(request()->query(), ['status' => 'cancelled'])) }}"
                    class="status-tab {{ request('status') === 'cancelled' ? 'bg-slate-600 text-white' : 'bg-slate-100 text-slate-600' }}">
                    <i class="fa-solid fa-ban me-1"></i> ยกเลิก
                </a>
            </div>
        </div>

        <!-- Ticket Table Card -->
        <div class="data-card">
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead>
                        @if (Auth::user()->isAdmin() && Auth::user()->role === 'staff')
                            <tr>
                                <th class="px-5 py-3.5">เลขที่แจ้งซ่อม</th>
                                <th class="px-5 py-3.5">วันที่แจ้ง</th>
                                <th class="px-5 py-3.5">ผู้แจ้ง / แผนก</th>
                                <th class="px-5 py-3.5">หมวดหมู่ & ครุภัณฑ์</th>
                                <th class="px-5 py-3.5">สถานที่ & อาการเสีย</th>
                                <th class="px-5 py-3.5">แนวทางการซ่อม</th>
                                <th class="px-5 py-3.5">สถานะ</th>
                                <th class="px-5 py-3.5">ผู้รับผิดชอบ</th>
                                <th class="px-5 py-3.5 text-right">การจัดการ</th>
                            </tr>
                        @else
                            <tr>
                                <th class="px-5 py-3.5">เลขที่แจ้งซ่อม</th>
                                <th class="px-5 py-3.5">วันที่แจ้ง</th>
                                <th class="px-5 py-3.5">หมวดหมู่ & ครุภัณฑ์</th>
                                <th class="px-5 py-3.5">สถานที่ & อาการเสีย</th>
                                <th class="px-5 py-3.5">แนวทางการซ่อม</th>
                                <th class="px-5 py-3.5">สถานะ</th>
                                <th class="px-5 py-3.5">ผู้รับผิดชอบ</th>
                                <th class="px-5 py-3.5 text-right">การจัดการ</th>
                            </tr>
                        @endif
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($tickets as $ticket)
                            <tr>
                                <td class="px-5 py-4">
                                    <span class="">{{ $ticket->ticket_no }}</span></br>
                                    @if ($ticket->images->count() > 0)
                                        <span
                                            class="inline-flex items-center gap-1 text-[10px] text-slate-400 mt-0.5"></span></br>
                                    @endif
                                    <span class="text-[11px] text-[#9e9e9e]"><i class="fa-solid fa-paperclip"></i> มี
                                        {{ $ticket->images->count() }} รูป</span>
                                </td>
                                <td class="px-5 py-4 text-xs text-slate-500">
                                    {{ $ticket->created_at ? $ticket->created_at->format('d/m/Y H:i') : '-' }}
                                </td>
                                @if (Auth::user()->isAdmin())
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-slate-800">
                                            {{ $ticket->reporter->name ?? 'ไม่ระบุ' }}</div>
                                        <div class="text-xs text-slate-400">
                                            {{ $ticket->reporter->department->department_name ?? '-' }}</div>
                                    </td>
                                @endif
                                <td class="px-5 py-4">
                                    <div class="font-medium text-slate-700">
                                        {{ $ticket->category->category_name ?? 'ทั่วไป' }}</div>
                                    @if ($ticket->asset_no)
                                        <div class="text-xs text-slate-400">รหัส: {{ $ticket->asset_no }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 max-w-xs">
                                    <div class="font-medium text-slate-800 truncate">
                                        <i
                                            class="fa-solid fa-location-dot text-rose-500 text-xs me-1"></i>{{ $ticket->location }}
                                    </div>
                                    <div class="text-xs text-slate-500 truncate mt-0.5" title="{{ $ticket->symptom }}">
                                        {{ $ticket->symptom }}
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span
                                        class="status-pill {{ $ticket->repair_method === 'external' ? 'status-pending' : ($ticket->repair_method === 'unrepairable' ? 'status-cancelled' : ($ticket->repair_method === 'in_house' ? 'status-completed' : '')) }}">
                                        {{ $ticket->repair_method_label }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    @if ($ticket->status === 'pending')
                                        <span class="status-pill status-pending">
                                            <span class="status-dot"></span>
                                            รอรับเรื่อง
                                        </span>
                                    @elseif($ticket->status === 'in_progress')
                                        <span class="status-pill status-progress">
                                            <span class="status-dot"></span>
                                            กำลังดำเนินการ
                                        </span>
                                    @elseif($ticket->status === 'completed')
                                        <span class="status-pill status-completed">
                                            <span class="status-dot"></span>
                                            เสร็จสิ้น
                                        </span>
                                    @else
                                        <span class="status-pill status-cancelled">
                                            <span class="status-dot"></span>
                                            ยกเลิก
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="text-xs text-slate-600">
                                        {{ $ticket->assignedStaff->name ?? 'ยังไม่มีผู้รับผิดชอบ' }}
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    @if (Auth::user()->role === 'staff' && $ticket->status === 'pending' && $ticket->assigned_staff_id === null)
                                        <form method="POST" action="{{ route('tickets.claim', $ticket->id) }}"
                                            class="mb-2" data-claim-ticket>
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-emerald-700">
                                                <i class="fa-solid fa-hand-point-up"></i>
                                                <span>รับงานนี้</span>
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('tickets.show', $ticket->id) }}"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-600 text-blue-600 hover:text-white text-xs font-semibold transition-all">
                                        <span>เปิดดู</span>
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ Auth::user()->isAdmin() ? 9 : 8 }}"
                                    class="px-5 py-12 text-center text-slate-400">
                                    <div class="empty-state-Group">
                                        <div class="empty-state-icon">
                                            <i class="fa-solid fa-magnifying-glass"></i>
                                        </div>
                                        <p class="font-medium text-slate-600">ไม่พบรายการแจ้งซ่อมตามเงื่อนไข</p>
                                        <p class="text-xs text-slate-400 mt-1">ลองเปลี่ยนคำค้นหา
                                            หรือล้างตัวกรองเพื่อค้นหาอีกครั้ง</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($tickets->hasPages())
                <div class="pagination-wrap">
                    {{ $tickets->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
