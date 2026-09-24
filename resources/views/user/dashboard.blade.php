<x-app-layout>
    <x-slot name="header">
        <div class="page-header">
            <div>
                <h2 class="page-title flex items-center gap-3">
                    <span>สวัสดีคุณ <span class="font-semibold text-[#059669]">{{ Auth::user()->name }}</span> </span>
                </h2>
                <p class="page-description">
                    ติดตามความคืบหน้ารายการแจ้งซ่อมของคุณ
                </p>
            </div>
            {{-- <a href="{{ route('tickets.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                <i class="fa-solid fa-plus"></i>
                แจ้งซ่อมใหม่
            </a> --}}
        </div>
    </x-slot>

    {{-- <div class="data-page-stack">
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <a href="{{ route('tickets.index') }}"
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">ทั้งหมด</span>
                    <i class="fa-solid fa-inbox text-slate-400"></i>
                </div>
                <div class="mt-3 text-3xl font-bold text-slate-800">{{ number_format($stats['total']) }}</div>
                <div class="mt-1 text-xs text-slate-400">รายการของฉัน</div>
            </a>

            <a href="{{ route('tickets.index', ['status' => 'pending']) }}"
                class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-amber-700">รอรับเรื่อง</span>
                    <i class="fa-solid fa-clock text-amber-500"></i>
                </div>
                <div class="mt-3 text-3xl font-bold text-amber-700">{{ number_format($stats['pending']) }}</div>
                <div class="mt-1 text-xs text-amber-700/70">รอเจ้าหน้าที่ตรวจสอบ</div>
            </a>

            <a href="{{ route('tickets.index', ['status' => 'in_progress']) }}"
                class="rounded-2xl border border-blue-200 bg-blue-50 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-blue-700">กำลังดำเนินการ</span>
                    <i class="fa-solid fa-screwdriver-wrench text-blue-500"></i>
                </div>
                <div class="mt-3 text-3xl font-bold text-blue-700">{{ number_format($stats['in_progress']) }}</div>
                <div class="mt-1 text-xs text-blue-700/70">กำลังแก้ไขปัญหา</div>
            </a>

            <a href="{{ route('tickets.index', ['status' => 'completed']) }}"
                class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-emerald-700">เสร็จสิ้น</span>
                    <i class="fa-solid fa-circle-check text-emerald-500"></i>
                </div>
                <div class="mt-3 text-3xl font-bold text-emerald-700">{{ number_format($stats['completed']) }}</div>
                <div class="mt-1 text-xs text-emerald-700/70">ซ่อมเสร็จสมบูรณ์</div>
            </a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <h3 class="flex items-center gap-2 font-bold text-slate-800">
                        <i class="fa-solid fa-clock-rotate-left text-emerald-600"></i>
                        รายการล่าสุดของฉัน
                    </h3>
                    <p class="mt-1 text-xs text-slate-500">ติดตามงานแจ้งซ่อม 5 รายการล่าสุด</p>
                </div>
                <a href="{{ route('tickets.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">
                    ดูทั้งหมด <i class="fa-solid fa-arrow-right ml-1"></i>
                </a>
            </div>

            @forelse ($tickets as $ticket)
                <a href="{{ route('tickets.show', $ticket->id) }}"
                    class="flex items-center justify-between gap-4 border-b border-slate-100 px-5 py-4 transition last:border-0 hover:bg-slate-50">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-emerald-700">{{ $ticket->ticket_no }}</span>
                            <span class="text-xs text-slate-400">{{ $ticket->category->category_name ?? 'ทั่วไป' }}</span>
                        </div>
                        <div class="mt-1 truncate text-sm text-slate-700">{{ $ticket->symptom }}</div>
                        <div class="mt-1 text-xs text-slate-400">
                            <i class="fa-solid fa-location-dot mr-1"></i>{{ $ticket->location }}
                        </div>
                    </div>
                    <div class="shrink-0 text-right">
                        @if ($ticket->status === 'pending')
                            <span class="status-pill status-pending"><span class="status-dot"></span>รอรับเรื่อง</span>
                        @elseif ($ticket->status === 'in_progress')
                            <span class="status-pill status-progress"><span class="status-dot"></span>กำลังดำเนินการ</span>
                        @elseif ($ticket->status === 'completed')
                            <span class="status-pill status-completed"><span class="status-dot"></span>เสร็จสิ้น</span>
                        @else
                            <span class="status-pill status-cancelled"><span class="status-dot"></span>ยกเลิก</span>
                        @endif
                        <div class="mt-2 text-xs text-slate-400">{{ $ticket->created_at?->format('d/m/Y H:i') }}</div>
                    </div>
                </a>
            @empty
                <div class="px-5 py-12 text-center text-slate-400">
                    <i class="fa-solid fa-inbox mb-3 text-3xl"></i>
                    <p class="font-medium text-slate-600">ยังไม่มีรายการแจ้งซ่อม</p>
                    <a href="{{ route('tickets.create') }}" class="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-emerald-600 hover:text-emerald-700">
                        <i class="fa-solid fa-plus"></i> แจ้งซ่อมรายการแรก
                    </a>
                </div>
            @endforelse
        </div>
    </div> --}}

</x-app-layout>
