<x-app-layout>
    <x-slot name="header">
        <div class="page-header">
            <div>
                @if (Auth::user()->isAdmin())
                    <h2 class="page-title">
                        {{-- <i class="fa-solid fa-chart-pie text-blue-600"></i> --}}
                        <span>แดชบอร์ดภาพรวมระบบแจ้งซ่อม</span>
                    </h2>
                @else
                    <h2 class="page-title">
                        {{-- <i class="fa-solid fa-clipboard-list text-emerald-600"></i> --}}
                        <span>แดชบอร์ดงานแจ้งซ่อมของฉัน</span>
                    </h2>
                @endif
                <p class="page-description">
                    {{ Auth::user()->isAdmin() ? 'ยินดีต้อนรับผู้ดูแลระบบคุณ' : 'ติดตามสถานะงานแจ้งซ่อมของคุณได้ที่หน้านี้' }}
                    <span class="font-semibold text-slate-700">{{ Auth::user()->name }}</span>
                    @if (!Auth::user()->isAdmin())
                        <span>({{ Auth::user()->department->department_name ?? 'ไม่ระบุแผนก' }})</span>
                    @endif
                </p>
            </div>
        </div>
    </x-slot>
    <div class="data-page-stack">
        <!-- Stats Grid -->
        <div class="dashboard-stats-grid">
                <div class="dashboard-stat-card stat-total">
                    <a href="{{ route('tickets.index') }}">
                        <div class="dashboard-stat-content">
                            <div>
                                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">
                                    รายการแจ้งซ่อมทั้งหมด
                                </p>
                                <h3 class="text-3xl font-bold text-slate-800 mt-1">{{ number_format($stats['total']) }}
                                </h3>
                                <p class="text-xs text-slate-400 mt-1">รายการในระบบ</p>
                            </div>
                            <div class="dashboard-stat-icon">
                                <i class="fa-solid fa-folder-open"></i>
                            </div>
                        </div>
                    </a>
                    <div class="dashboard-stat-accent"></div>
                </div>

                <!-- Pending -->
                <div class="dashboard-stat-card stat-pending">
                    <a href="{{ route('tickets.index', ['status' => 'pending']) }}">
                        <div class="dashboard-stat-content">
                            <div>
                                <p class="text-xs font-medium text-amber-600 uppercase tracking-wider">รอรับเรื่อง /
                                    รอดำเนินการ
                                </p>
                                <h3 class="text-3xl font-bold text-amber-600 mt-1">
                                    {{ number_format($stats['pending']) }}
                                </h3>
                                <p class="text-xs text-amber-600/70 mt-1">รอเจ้าหน้าที่ตรวจสอบ</p>
                            </div>
                            <div class="dashboard-stat-icon">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                        </div>
                    </a>
                    <div class="dashboard-stat-accent"></div>
                </div>

                <!-- กำลังดำเนินการ -->
                <div class="dashboard-stat-card stat-progress">
                    <a href="{{ route('tickets.index', ['status' => 'in_progress']) }}">
                        <div class="dashboard-stat-content">
                            <div>
                                <p class="text-xs font-medium text-blue-600 uppercase tracking-wider">กำลังดำเนินการซ่อม
                                </p>
                                <h3 class="text-3xl font-bold text-blue-600 mt-1">
                                    {{ number_format($stats['in_progress']) }}
                                </h3>
                                <p class="text-xs text-blue-600/70 mt-1">กำลังแก้ไขปัญหา</p>
                            </div>
                            <div class="dashboard-stat-icon">
                                <i class="fa-solid fa-screwdriver-wrench"></i>
                            </div>
                        </div>
                        <div class="dashboard-stat-accent"></div>
                    </a>
                </div>

                <!-- เสร็จสิ้น -->
                <div class="dashboard-stat-card stat-completed">
                    <a href="{{ route('tickets.index', ['status' => 'completed']) }}">
                        <div class="dashboard-stat-content">
                            <div>
                                <p class="text-xs font-medium text-emerald-600 uppercase tracking-wider">แก้ไขเสร็จสิ้น
                                </p>
                                <h3 class="text-3xl font-bold text-emerald-600 mt-1">
                                    {{ number_format($stats['completed']) }}
                                </h3>
                                <p class="text-xs text-emerald-600/70 mt-1">ซ่อมเสร็จสมบูรณ์</p>
                            </div>
                            <div class="dashboard-stat-icon">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                        </div>
                        <div class="dashboard-stat-accent"></div>
                    </a>
                </div>

                <div class="dashboard-stat-card stat-cancelled">
                    <a href="{{ route('tickets.index', ['status' => 'cancelled']) }}">
                        <div class="dashboard-stat-content">
                            <div>
                                <p class="text-xs font-medium text-red-600 uppercase tracking-wider">ยกเลิกรายการ</p>
                                <h3 class="text-3xl font-bold text-red-600 mt-1">
                                    {{ number_format($stats['cancelled']) }}
                                </h3>
                                <p class="text-xs text-red-600/70 mt-1">รายการที่ถูกยกเลิก</p>
                            </div>
                            <div class="dashboard-stat-icon">
                                <i class="fa-solid fa-ban"></i>
                            </div>
                        </div>
                        <div class="dashboard-stat-accent"></div>
                    </a>
                </div>
            </div>

            {{-- กราฟสรุปตามหมวดหมู่งานซ่อม --}}
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

            <div class="dashboard-chart-grid">
                <div class="report-category-card">
                    <div class="report-chart-header">
                        <div>
                            <h2 class="section-title text-base font-bold text-slate-800"><i
                                    class="fa-solid fa-chart-pie text-blue-600"></i>สรุปตามหมวดหมู่งานซ่อม</h2>
                            <p class="report-chart-description">สัดส่วนประเภทงานซ่อม</p>
                        </div>
                        <span class="report-chart-total">รวม {{ number_format($categoryTotal) }} รายการ</span>
                    </div>

                    @if ($categorySummary->isNotEmpty())
                        <div class="category-chart-content">
                            <div class="category-pie-chart"
                                style="background: conic-gradient({{ implode(', ', $categorySegments) }})"
                                role="img" aria-label="กราฟวงกลมสรุปตามหมวดหมู่งานซ่อม">
                                @php $labelOffset = 0; @endphp
                                @foreach ($categorySummary as $index => $category)
                                    @php
                                        $categoryPercent =
                                            $categoryTotal > 0 ? ($category['total'] / $categoryTotal) * 100 : 0;
                                        $labelAngle = ($labelOffset + $categoryPercent / 2) * 3.6;
                                        $labelX = 50 + 34 * sin(deg2rad($labelAngle));
                                        $labelY = 50 - 34 * cos(deg2rad($labelAngle));
                                        $labelOffset += $categoryPercent;
                                    @endphp
                                    <span class="category-pie-label"
                                        style="left: {{ $labelX }}%; top: {{ $labelY }}%">
                                        {{ number_format($category['total']) }}
                                        <small>{{ number_format($categoryPercent, 1) }}%</small>
                                    </span>
                                @endforeach
                            </div>
                            <div class="category-chart-legend" role="list" aria-label="รายละเอียดหมวดหมู่งานซ่อม">
                                @foreach ($categorySummary as $index => $category)
                                    @php
                                        $categoryPercent =
                                            $categoryTotal > 0 ? ($category['total'] / $categoryTotal) * 100 : 0;
                                    @endphp
                                    <div class="category-legend-item" role="listitem">
                                        <span class="category-legend-label"><span class="category-legend-dot"
                                                style="background: {{ $categoryColors[$index % count($categoryColors)] }}"></span>{{ $category['label'] }}</span>
                                        <span class="category-legend-value">{{ number_format($category['total']) }}
                                            <small>({{ number_format($categoryPercent, 1) }}%)</small></span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <p class="empty-state category-chart-empty">ไม่พบข้อมูลสำหรับสร้างกราฟ</p>
                    @endif
                </div>

                {{-- กราฟบอกสถานะ --}}
                @php
                    $statusColors = ['#f59e0b', '#2563eb', '#10b981', '#f43f5e'];
                    $statusTotal = $statusSummary->sum('total');
                    $statusOffset = 0;
                    $statusSegments = [];

                    foreach ($statusSummary as $index => $status) {
                        $statusPercent = $statusTotal > 0 ? ($status['total'] / $statusTotal) * 100 : 0;
                        $statusSegments[] = sprintf(
                            '%s %s%% %s%%',
                            $statusColors[$index % count($statusColors)],
                            $statusOffset,
                            $statusOffset + $statusPercent,
                        );
                        $statusOffset += $statusPercent;
                    }
                @endphp

                <div class="report-category-card">
                    <div class="report-chart-header">
                        <div>
                            <h2 class="section-title text-base font-bold text-slate-800"><i
                                    class="fa-solid fa-chart-column text-emerald-600"></i>สรุปตามสถานะงาน</h2>
                            <p class="report-chart-description">จำนวนงานแจ้งซ่อมแยกตามสถานะปัจจุบัน</p>
                        </div>
                        <span class="report-chart-total">รวม {{ number_format($statusTotal) }} รายการ</span>
                    </div>

                    @if ($statusTotal > 0)
                        <div class="category-chart-content">
                            <div class="category-pie-chart"
                                style="background: conic-gradient({{ implode(', ', $statusSegments) }})" role="img"
                                aria-label="กราฟวงกลมสรุปตามสถานะงาน">
                                @php $statusLabelOffset = 0; @endphp
                                @foreach ($statusSummary as $index => $status)
                                    @php
                                        $statusPercent = ($status['total'] / $statusTotal) * 100;
                                        $labelAngle = ($statusLabelOffset + $statusPercent / 2) * 3.6;
                                        $labelX = 50 + 34 * sin(deg2rad($labelAngle));
                                        $labelY = 50 - 34 * cos(deg2rad($labelAngle));
                                        $statusLabelOffset += $statusPercent;
                                    @endphp
                                    @if ($status['total'] > 0)
                                        <span class="category-pie-label"
                                            style="left: {{ $labelX }}%; top: {{ $labelY }}%">
                                            {{ number_format($status['total']) }}
                                            <small>{{ number_format($statusPercent, 1) }}%</small>
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                            <div class="category-chart-legend" role="list" aria-label="รายละเอียดสถานะงาน">
                                @foreach ($statusSummary as $index => $status)
                                    @php $statusPercent = $statusTotal > 0 ? ($status['total'] / $statusTotal) * 100 : 0; @endphp
                                    <div class="category-legend-item" role="listitem">
                                        <span class="category-legend-label"><span class="category-legend-dot"
                                                style="background: {{ $statusColors[$index % count($statusColors)] }}"></span>{{ $status['label'] }}</span>
                                        <span class="category-legend-value">{{ number_format($status['total']) }}
                                            <small>({{ number_format($statusPercent, 1) }}%)</small></span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <p class="empty-state category-chart-empty">ไม่พบข้อมูลสำหรับสร้างกราฟ</p>
                    @endif
                </div>
            </div>

            <!-- Recent Tickets Table Card -->
            {{-- <div class="data-card">
            <div class="data-card-header">
                <div>
                    <h3 class="section-title">
                        <i class="fa-solid fa-clock-rotate-left text-slate-400"></i>
                        <span>{{ Auth::user()->isAdmin() ? 'รายการแจ้งซ่อมล่าสุด' : 'งานแจ้งซ่อมล่าสุดของฉัน' }}</span>
                    </h3>
                    <p class="text-xs text-slate-500">
                        {{ Auth::user()->isAdmin() ? 'แสดงรายการแจ้งซ่อม 5 รายการล่าสุดในระบบ' : 'แสดงงานแจ้งซ่อม 5 รายการล่าสุดของคุณ' }}
                    </p>
                </div>
                <a href="{{ route('tickets.index') }}"
                    class="text-xs font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1">
                    <span>ดูทั้งหมด</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            <div class="data-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="px-5 py-3">เลขที่ใบแจ้งซ่อม</th>
                            @if (Auth::user()->isAdmin())
                                <th class="px-5 py-3">ผู้แจ้ง / แผนก</th>
                            @endif
                            <th class="px-5 py-3">หมวดหมู่</th>
                            <th class="px-5 py-3">สถานที่ / ปัญหา</th>
                            <th class="px-5 py-3">สถานะ</th>
                            <th class="px-5 py-3">วันที่แจ้ง</th>
                            <th class="px-5 py-3 text-right">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                            <tr>
                                <td class="px-5 py-4 font-semibold text-blue-600">
                                    {{ $ticket->ticket_no }}
                                </td>
                                @if (Auth::user()->isAdmin())
                                    <td class="px-5 py-4">
                                        <div class="font-medium text-slate-800">
                                            {{ $ticket->reporter->name ?? 'ไม่ระบุ' }}</div>
                                        <div class="text-xs text-slate-400">
                                            {{ $ticket->reporter->department->department_name ?? '-' }}</div>
                                    </td>
                                @endif
                                <td class="px-5 py-4">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $ticket->category->category_name ?? 'ทั่วไป' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 max-w-xs">
                                    <div class="font-medium text-slate-800 truncate"><i
                                            class="fa-solid fa-location-dot text-rose-500 text-xs me-1"></i>{{ $ticket->location }}
                                    </div>
                                    <div class="text-xs text-slate-500 truncate mt-0.5">{{ $ticket->symptom }}</div>
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
                                <td class="px-5 py-4 text-xs text-slate-500">
                                    {{ $ticket->created_at ? $ticket->created_at->format('d/m/Y H:i') : '-' }}
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('tickets.show', $ticket->id) }}" class="secondary-action">
                                        <span>รายละเอียด</span>
                                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ Auth::user()->isAdmin() ? 7 : 6 }}"
                                    class="px-5 py-12 text-center text-slate-400">
                                    <div class="empty-state-icon">
                                        <i class="fa-solid fa-inbox"></i>
                                    </div>
                                    <p class="font-medium text-slate-600">ยังไม่มีรายการแจ้งซ่อมในระบบ</p>
                                    <p class="text-xs text-slate-400 mt-1">
                                        คลิกปุ่มด้านล่างเพื่อเริ่มแจ้งซ่อมเป็นรายการแรก</p>
                                    <a href="{{ route('tickets.create') }}"
                                        class="inline-block mt-4 px-4 py-2 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-500">
                                        + แจ้งซ่อมใหม่
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div> --}}

            <div class="dashboard-chartDetail-grid">
                <div class="data-card">
                    <div class="data-card-header">
                        <div>
                            <h2 class="section-title text-base font-bold text-slate-800">
                                <span>หมวดหมู่งานซ่อม</span>
                            </h2>
                            <p class="text-xs text-slate-500">รายการหมวดหมู่งานซ่อมทั้งหมดในระบบ</p>
                        </div>
                    </div>

                    <div class="data-table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>หมวดหมู่</th>
                                    <th class="text-right">จำนวนรายการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($categories as $category)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="font-semibold text-slate-800">{{ $category->category_name }}</td>
                                        <td class="text-right font-semibold text-blue-600">
                                            {{ number_format($category->ticket_count) }} รายการ
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-slate-400">
                                            ไม่พบหมวดหมู่งานซ่อมในระบบ
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="data-card">
                    <div class="data-card-header">
                        <div>
                            <h2 class="section-title text-base font-bold text-slate-800">
                                <span>สถานะงาน</span>
                            </h2>
                            <p class="text-xs text-slate-500">รายการสถานะงานทั้งหมดในระบบ</p>
                        </div>
                    </div>

                    <div class="data-table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>สถานะ</th>
                                    <th class="text-right">จำนวนรายการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($statusSummary as $status)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="font-semibold text-slate-800">{{ $status['label'] }}</td>
                                        <td class="text-right font-semibold text-blue-600">
                                            {{ number_format($status['total']) }} รายการ
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-slate-400">
                                            ไม่พบสถานะงานในระบบ
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
</x-app-layout>
