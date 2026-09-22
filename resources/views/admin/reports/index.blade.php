<x-app-layout>
    <x-slot name="header">
        <div class="page-header">
            <div>
                <h1 class="page-title">รายงาน / สรุปยอด</h1>
                <p class="page-description">สรุปภาพรวมรายการแจ้งซ่อมตามช่วงเวลาและเงื่อนไขที่เลือก</p>
            </div>
        </div>
    </x-slot>

    <div class="data-page-stack">
        <div class="filter-panel">
            <form method="GET" action="{{ route('admin.reports.index') }}" class="filter-form">
                <div>
                    <label for="from" class="mb-1 block text-xs font-semibold text-slate-500">ตั้งแต่วันที่</label>
                    <input id="from" name="from" type="date" value="{{ request('from') }}"
                        class="filter-input">
                </div>
                <div>
                    <label for="to" class="mb-1 block text-xs font-semibold text-slate-500">ถึงวันที่</label>
                    <input id="to" name="to" type="date" value="{{ request('to') }}"
                        class="filter-input">
                </div>
                <div>
                    <label for="status" class="mb-1 block text-xs font-semibold text-slate-500">สถานะ</label>
                    <select id="status" name="status" class="filter-select">
                        <option value="">ทุกสถานะ</option>
                        <option value="pending" @selected(request('status') === 'pending')>รอรับเรื่อง</option>
                        <option value="in_progress" @selected(request('status') === 'in_progress')>กำลังดำเนินการ</option>
                        <option value="completed" @selected(request('status') === 'completed')>เสร็จสิ้น</option>
                        <option value="cancelled" @selected(request('status') === 'cancelled')>ยกเลิก</option>
                    </select>
                </div>
                <div>
                    <label for="category_id" class="mb-1 block text-xs font-semibold text-slate-500">หมวดหมู่</label>
                    <select id="category_id" name="category_id" class="filter-select">
                        <option value="">ทุกหมวดหมู่</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>
                                {{ $category->category_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="department_id" class="mb-1 block text-xs font-semibold text-slate-500">แผนก</label>
                    <select id="department_id" name="department_id" class="filter-select">
                        <option value="">ทุกแผนก</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected((string) request('department_id') === (string) $department->id)>
                                {{ $department->department_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-actions self-end">
                    <button type="submit" class="primary-action"><i class="fa-solid fa-filter"></i>กรองข้อมูล</button>
                    <a href="{{ route('admin.reports.index') }}" class="secondary-action">ล้าง</a>
                </div>
            </form>
        </div>

        <div class="dashboard-stats-grid">
            <a href="{{ route('admin.reports.index', array_merge(request()->query(), ['status' => ''])) }}">
                <div class="dashboard-stat-card stat-total {{ request()->filled('status') ? '' : 'is-active' }}">
                    <div class="dashboard-stat-content">
                        <div>
                            <p class="text-xs font-semibold text-slate-500">ทั้งหมด</p>
                            <p class="mt-1 text-2xl font-bold text-slate-800">{{ $summary['total'] }}</p>
                        </div>
                        <div class="dashboard-stat-icon"><i class="fa-solid fa-layer-group"></i></div>
                    </div>
                </div>
            </a>
            {{-- สถานะรอรับเรื่อง --}}
            <a href="{{ route('admin.reports.index', array_merge(request()->query(), ['status' => 'pending'])) }}">
            <div class="dashboard-stat-card stat-pending {{ request('status') === 'pending' ? 'is-active' : '' }}">
                <div class="dashboard-stat-content">
                    <div>
                        <p class="text-xs font-semibold text-slate-500">รอรับเรื่อง</p>
                        <p class="mt-1 text-2xl font-bold text-slate-800">{{ $summary['pending'] }}</p>
                    </div>
                    <div class="dashboard-stat-icon"><i class="fa-solid fa-hourglass-half"></i></div>
                </div>
            </div>
            </a>
            {{-- สถานะกำลังดำเนินการ --}}
            <a href="{{ route('admin.reports.index', array_merge(request()->query(), ['status' => 'in_progress'])) }}">
            <div class="dashboard-stat-card stat-progress {{ request('status') === 'in_progress' ? 'is-active' : '' }}">
                <div class="dashboard-stat-content">
                    <div>
                        <p class="text-xs font-semibold text-slate-500">กำลังดำเนินการ</p>
                        <p class="mt-1 text-2xl font-bold text-slate-800">{{ $summary['in_progress'] }}</p>
                    </div>
                    <div class="dashboard-stat-icon"><i class="fa-solid fa-screwdriver-wrench"></i></div>
                </div>
            </div>
            </a>
            {{-- สถานะเสร็จสิ้น --}}
            <a href="{{ route('admin.reports.index', array_merge(request()->query(), ['status' => 'completed'])) }}">
            <div class="dashboard-stat-card stat-completed {{ request('status') === 'completed' ? 'is-active' : '' }}">
                <div class="dashboard-stat-content">
                    <div>
                        <p class="text-xs font-semibold text-slate-500">เสร็จสิ้น</p>
                        <p class="mt-1 text-2xl font-bold text-slate-800">{{ $summary['completed'] }}</p>
                    </div>
                    <div class="dashboard-stat-icon"><i class="fa-solid fa-circle-check"></i></div>
                </div>
            </div>
            </a>
            {{-- สถานะยกเลิก --}}
            <a href="{{ route('admin.reports.index', array_merge(request()->query(), ['status' => 'cancelled'])) }}">
            <div class="dashboard-stat-card stat-cancelled {{ request('status') === 'cancelled' ? 'is-active' : '' }}">
                <div class="dashboard-stat-content">
                    <div>
                        <p class="text-xs font-semibold text-slate-500">ยกเลิก</p>
                        <p class="mt-1 text-2xl font-bold text-slate-800">{{ $summary['cancelled'] }}</p>
                    </div>
                    <div class="dashboard-stat-icon"><i class="fa-solid fa-ban"></i></div>
                </div>
            </div>
            </a>
        </div>

        <div class="data-card">
            <div class="data-card-header">
                <h2 class="section-title text-base font-bold text-slate-800"><i
                        class="fa-solid fa-table-list text-blue-600"></i>รายการแจ้งซ่อม</h2>
                <div class="data-card-actions"><span class="text-xs text-slate-500">{{ $tickets->total() }}
                        รายการ</span><a
                        href="{{ route('admin.users.export-tickets', request()->only(['status', 'category_id', 'department_id', 'from', 'to'])) }}"
                        class="secondary-action"><i class="fa-solid fa-file-excel"></i>Export งานซ่อม</a></div>
            </div>
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>เลขที่</th>
                            <th>ผู้แจ้ง</th>
                            <th>หมวดหมู่</th>
                            <th>แผนก</th>
                            <th>สถานะ</th>
                            <th>วันที่แจ้ง</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tickets as $ticket)
                            <tr>
                                <td><a class="table-link"
                                        href="{{ route('tickets.show', $ticket->id) }}">{{ $ticket->ticket_no }}</a>
                                </td>
                                <td>{{ $ticket->reporter->name ?? '-' }}</td>
                                <td>{{ $ticket->category->category_name ?? '-' }}</td>
                                <td>{{ $ticket->reporter->department->department_name ?? '-' }}</td>
                                <td><span
                                        class="status-pill status-{{ $ticket->status === 'in_progress' ? 'progress' : $ticket->status }}"><span
                                            class="status-dot"></span>{{ match ($ticket->status) {'pending' => 'รอรับเรื่อง','in_progress' => 'กำลังดำเนินการ','completed' => 'เสร็จสิ้น','cancelled' => 'ยกเลิก',default => $ticket->status} }}</span>
                                </td>
                                <td>{{ $ticket->created_at?->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty-state">ไม่พบรายการตามเงื่อนไข</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($tickets->hasPages())
                <div class="pagination-wrap">{{ $tickets->links() }}</div>
            @endif
        </div>

        @php
            $chartStatuses = [
                ['key' => 'pending', 'label' => 'รอรับเรื่อง', 'class' => 'chart-pending'],
                ['key' => 'in_progress', 'label' => 'กำลังดำเนินการ', 'class' => 'chart-progress'],
                ['key' => 'completed', 'label' => 'เสร็จสิ้น', 'class' => 'chart-completed'],
                ['key' => 'cancelled', 'label' => 'ยกเลิก', 'class' => 'chart-cancelled'],
            ];
        @endphp

        <div class="report-chart-card">
            <div class="report-chart-header">
                <div>
                    <h2 class="section-title text-base font-bold text-slate-800"><i
                            class="fa-solid fa-chart-column text-blue-600"></i>สรุปตามสถานะ</h2>
                    <p class="report-chart-description">สัดส่วนรายการแจ้งซ่อมตามเงื่อนไขที่เลือก</p>
                </div>
                <span class="report-chart-total">รวม {{ number_format($summary['total']) }} รายการ</span>
            </div>

            <div class="report-chart" role="list" aria-label="กราฟสรุปรายการแจ้งซ่อมตามสถานะ">
                @foreach ($chartStatuses as $chartStatus)
                    @php
                        $chartValue = $summary[$chartStatus['key']];
                        $chartPercent = $summary['total'] > 0 ? ($chartValue / $summary['total']) * 100 : 0;
                    @endphp
                    <div class="report-chart-row" role="listitem">
                        <div class="report-chart-label">
                            <span class="report-chart-dot {{ $chartStatus['class'] }}"></span>
                            <span>{{ $chartStatus['label'] }}</span>
                        </div>
                        <div class="report-chart-track" aria-hidden="true">
                            <div class="report-chart-bar {{ $chartStatus['class'] }}" style="width: {{ $chartPercent }}%"></div>
                        </div>
                        <div class="report-chart-value">{{ number_format($chartValue) }} <span>({{ number_format($chartPercent, 1) }}%)</span></div>
                    </div>
                @endforeach
            </div>
        </div>

        @php
            $categoryColors = ['#2563eb', '#10b981', '#f59e0b', '#f43f5e', '#8b5cf6', '#06b6d4', '#64748b'];
            $categoryTotal = $categorySummary->sum('total');
            $categoryOffset = 0;
            $categorySegments = [];

            foreach ($categorySummary as $index => $category) {
                $categoryPercent = $categoryTotal > 0 ? ($category['total'] / $categoryTotal) * 100 : 0;
                $categorySegments[] = sprintf(
                    '%s %s%% %s%%',
                    $categoryColors[$index % count($categoryColors)],
                    $categoryOffset,
                    $categoryOffset + $categoryPercent,
                );
                $categoryOffset += $categoryPercent;
            }
        @endphp

        <div class="report-category-card">
            <div class="report-chart-header">
                <div>
                    <h2 class="section-title text-base font-bold text-slate-800"><i
                            class="fa-solid fa-chart-pie text-blue-600"></i>สรุปตามหมวดหมู่งานซ่อม</h2>
                    <p class="report-chart-description">สัดส่วนประเภทงานซ่อมตามเงื่อนไขที่เลือก</p>
                </div>
                <span class="report-chart-total">รวม {{ number_format($categoryTotal) }} รายการ</span>
            </div>

            @if ($categorySummary->isNotEmpty())
                <div class="category-chart-content">
                    <div class="category-pie-chart" style="background: conic-gradient({{ implode(', ', $categorySegments) }})"
                        role="img" aria-label="กราฟวงกลมสรุปตามหมวดหมู่งานซ่อม">
                        @php $labelOffset = 0; @endphp
                        @foreach ($categorySummary as $index => $category)
                            @php
                                $categoryPercent = $categoryTotal > 0 ? ($category['total'] / $categoryTotal) * 100 : 0;
                                $labelAngle = ($labelOffset + ($categoryPercent / 2)) * 3.6;
                                $labelX = 50 + (34 * sin(deg2rad($labelAngle)));
                                $labelY = 50 - (34 * cos(deg2rad($labelAngle)));
                                $labelOffset += $categoryPercent;
                            @endphp
                            <span class="category-pie-label" style="left: {{ $labelX }}%; top: {{ $labelY }}%">
                                {{ number_format($category['total']) }}
                                <small>{{ number_format($categoryPercent, 1) }}%</small>
                            </span>
                        @endforeach
                    </div>
                    <div class="category-chart-legend" role="list" aria-label="รายละเอียดหมวดหมู่งานซ่อม">
                        @foreach ($categorySummary as $index => $category)
                            @php
                                $categoryPercent = $categoryTotal > 0 ? ($category['total'] / $categoryTotal) * 100 : 0;
                            @endphp
                            <div class="category-legend-item" role="listitem">
                                <span class="category-legend-label"><span class="category-legend-dot"
                                        style="background: {{ $categoryColors[$index % count($categoryColors)] }}"></span>{{ $category['label'] }}</span>
                                <span class="category-legend-value">{{ number_format($category['total']) }} <small>({{ number_format($categoryPercent, 1) }}%)</small></span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <p class="empty-state category-chart-empty">ไม่พบข้อมูลสำหรับสร้างกราฟ</p>
            @endif
        </div>
    </div>
</x-app-layout>
