<x-app-layout>
    <x-slot name="header">
        <div class="page-header">
            <div class="page-title-group">
                <a href="{{ route('tickets.index') }}"
                    class="secondary-action ticket-back-button">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div>
                    <h2 class="page-title">
                        <span>ใบแจ้งซ่อมเลขที่</span>
                        <span class=" text-blue-600">{{ $ticket->ticket_no }}</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        สร้างเมื่อ: {{ $ticket->created_at ? $ticket->created_at->format('d/m/Y H:i น.') : '-' }}
                    </p>
                </div>
            </div>

            <div class="ticket-status-actions">
                @if ($ticket->status === 'pending')
                    <span
                        class="px-4 py-1.5 rounded-full text-sm font-semibold bg-amber-100 text-amber-800 border border-amber-300 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        สถานะ: รอรับเรื่อง
                    </span>
                @elseif($ticket->status === 'in_progress')
                    <span
                        class="px-4 py-1.5 rounded-full text-sm font-semibold bg-blue-100 text-blue-800 border border-blue-300 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-500 animate-ping"></span>
                        สถานะ: กำลังดำเนินการซ่อม
                    </span>
                @elseif($ticket->status === 'completed')
                    <span
                        class="px-4 py-1.5 rounded-full text-sm font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-2">
                        <i class="fa-solid fa-circle-check"></i>
                        สถานะ: ซ่อมเสร็จสิ้น
                    </span>
                @else
                    <span
                        class="px-4 py-1.5 rounded-full text-sm font-semibold bg-slate-200 text-slate-700 flex items-center gap-2">
                        <i class="fa-solid fa-ban"></i>
                        สถานะ: ยกเลิก
                    </span>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="data-page-stack">
        {{-- ไทม์ไลน์แสดงกระบวนการทำงานทุกขั้นตอน --}}
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-8 flex items-center gap-2">
                <i class="fa-solid fa-timeline text-blue-600"></i>
                <span>ขั้นตอนการดำเนินงาน (Status Timeline)</span>
            </h3>
            <div class="relative">
                {{-- ตัวแสดงขั้นตอนการทำงาน --}}
                <div class="hidden md:block absolute top-5 left-[12%] right-[12%] h-1 bg-slate-200 -z-0">
                    @php
                        $progressWidth = '0%';
                        if (in_array($ticket->status, ['completed'])) {
                            $progressWidth = '100%';
                        } elseif ($ticket->status === 'in_progress') {
                            $progressWidth = '66.66%';
                        } elseif ($ticket->status === 'pending') {
                            $progressWidth = '33.33%';
                        }
                    @endphp
                    <div class="h-full bg-green-600 transition-all duration-500 progress-width-{{ str_replace('.', '-', $progressWidth) }}">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 relative z-10">

                    <!-- Step 1: สร้างเรื่องแจ้งซ่อม -->
                    <div class="flex md:flex-col items-center gap-4 md:text-center">
                        <div
                            class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-md ring-4 ring-white">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <div>
                            <div class="font-bold text-sm text-slate-800">1. ยื่นเรื่องแจ้งซ่อม</div>
                            <div class="text-xs text-emerald-600 font-medium">ส่งข้อมูลเข้าระบบแล้ว</div>
                        </div>
                    </div>

                    <!-- Step 2: รอเจ้าหน้าที่ -->
                    <div class="flex md:flex-col items-center gap-4 md:text-center">
                        <div
                            class="w-10 h-10 rounded-full {{ $ticket->status === 'pending' ? 'border-4 border-emerald-600 bg-white text-emerald-600 shadow-md' : (in_array($ticket->status, ['in_progress', 'completed']) ? 'bg-emerald-600 text-white shadow-md ring-4 ring-white' : 'bg-slate-200 text-slate-500') }} flex items-center justify-center font-bold text-sm shrink-0">
                            @if (in_array($ticket->status, ['in_progress', 'completed']))
                                <i class="fa-solid fa-check"></i>
                            @elseif ($ticket->status === 'pending')
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                            @else
                                2
                            @endif
                        </div>
                        <div>
                            <div class="font-bold text-sm text-slate-800">2. รับเรื่องตรวจสอบ</div>
                            <div class="text-xs text-slate-500">
                                {{ $ticket->status === 'pending' ? 'รอเจ้าหน้าที่รับงาน' : 'เจ้าหน้าที่รับทราบแล้ว' }}
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: กำลังดำเนินการ -->
                    <div class="flex md:flex-col items-center gap-4 md:text-center">
                        <div
                            class="w-10 h-10 rounded-full {{ $ticket->status === 'in_progress' ? 'border-4 border-emerald-600 bg-white text-emerald-600 shadow-md' : ($ticket->status === 'completed' ? 'bg-emerald-600 text-white shadow-md ring-4 ring-white' : 'bg-slate-200 text-slate-500') }} flex items-center justify-center font-bold text-sm shrink-0">
                            @if ($ticket->status === 'completed')
                                <i class="fa-solid fa-check"></i>
                            @elseif ($ticket->status === 'in_progress')
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                            @else
                                3
                            @endif
                        </div>
                        <div>
                            <div class="font-bold text-sm text-slate-800">3. กำลังซ่อมแซม</div>
                            <div class="text-xs text-slate-500">
                                {{ $ticket->status === 'in_progress' ? 'เจ้าหน้าที่กำลังซ่อมบำรุง' : ($ticket->status === 'completed' ? 'ซ่อมเสร็จแล้ว' : 'รอดำเนินการ') }}
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: เสร็จสิ้น -->
                    <div class="flex md:flex-col items-center gap-4 md:text-center">
                        <div
                            class="w-10 h-10 rounded-full {{ $ticket->status === 'completed' ? 'border-4 border-emerald-600 bg-white text-emerald-600 shadow-md' : 'bg-slate-200 text-slate-500' }} flex items-center justify-center font-bold text-sm shrink-0">
                            @if ($ticket->status === 'completed')
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                            @else
                                4
                            @endif
                        </div>
                        <div>
                            <div class="font-bold text-sm text-slate-800">4. เสร็จสมบูรณ์</div>
                            <div class="text-xs text-slate-500">
                                {{ $ticket->status === 'completed' ? 'ส่งมอบงานเรียบร้อย' : 'รอดำเนินการ' }}
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- ซ้าย ข้อมูลรายละเอียด -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-6">
                    <h3 class="font-bold text-lg text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-circle-info text-blue-600"></i>
                        <span>รายละเอียดการแจ้งซ่อม</span>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div class="p-3 bg-slate-50 rounded-xl">
                            <span class="text-xs text-slate-400 font-medium block uppercase">หมวดหมู่ปัญหา</span>
                            <span class="font-semibold text-slate-800 text-base">
                                {{ $ticket->category->category_name ?? 'ไม่ระบุหมวดหมู่' }}
                            </span>
                        </div>

                        <div class="p-3 bg-slate-50 rounded-xl">
                            <span class="text-xs text-slate-400 font-medium block uppercase">รหัสครุภัณฑ์ /
                                อุปกรณ์</span>
                            <span class="font-semibold text-slate-800 text-base">
                                {{ $ticket->asset_no ?: 'ไม่มี / ไม่ได้ระบุ' }}
                            </span>
                        </div>

                        <div class="sm:col-span-2 p-3 bg-slate-50 rounded-xl">
                            <span class="text-xs text-slate-400 font-medium block uppercase">สถานที่ / อาคาร /
                                ห้อง</span>
                            <span class="font-semibold text-slate-800 text-base flex items-center gap-1.5 mt-0.5">
                                <i class="fa-solid fa-location-dot text-rose-500"></i>
                                {{ $ticket->location }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">อาการเสีย /
                            ปัญหาที่พบ</h4>
                        <div
                            class="p-4 bg-slate-50 border border-slate-200/60 rounded-xl text-slate-800 text-sm leading-relaxed whitespace-pre-line">
                            {{ $ticket->symptom }}
                        </div>
                    </div>

                    <!-- รูป -->
                    <div>
                        <h4
                            class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 flex items-center justify-between">
                            <span>รูปภาพประกอบ ({{ $ticket->images->count() }} รูป)</span>
                        </h4>

                        @if ($ticket->images->count() > 0)
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                                @foreach ($ticket->images as $img)
                                    <a href="{{ asset('storage/' . $img->image_path) }}" target="_blank"
                                        class="group relative rounded-xl overflow-hidden border border-slate-200 aspect-video bg-slate-100 block shadow-sm hover:shadow-md transition-shadow">
                                        <img src="{{ asset('storage/' . $img->image_path) }}" alt="รูปภาพปัญหา"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                        <div
                                            class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs gap-1 font-medium">
                                            <i class="fa-solid fa-magnifying-glass-plus"></i>
                                            <span>ขยายรูปภาพ</span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div
                                class="p-6 bg-slate-50 rounded-xl text-center text-slate-400 text-xs border border-dashed border-slate-200">
                                <i class="fa-regular fa-image text-2xl mb-1 block"></i>
                                ไม่มีรูปภาพแนบในรายการนี้
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Reporter Info Card -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
                    <h3
                        class="font-bold text-base text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-user-circle text-blue-600"></i>
                        <span>ข้อมูลผู้แจ้งซ่อม</span>
                    </h3>

                    <div class="flex items-center gap-3">
                        <div
                            class="w-12 h-12 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-lg">
                            {{ mb_substr($ticket->reporter->name ?? 'U', 0, 1) }}
                        </div>
                        <div>
                            <div class="font-bold text-slate-800">{{ $ticket->reporter->name ?? 'ไม่พบข้อมูล' }}</div>
                            <div class="text-xs text-slate-500">{{ $ticket->reporter->username ?? '' }}</div>
                        </div>
                    </div>

                    <div class="space-y-2 text-xs pt-2 border-t border-slate-100 text-slate-600">
                        <div class="flex justify-between">
                            <span class="text-slate-400">แผนก/สังกัด:</span>
                            <span
                                class="font-medium text-slate-800">{{ $ticket->reporter->department->department_name ?? 'ไม่ระบุ' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">เบอร์โทรศัพท์:</span>
                            <span
                                class="font-medium text-slate-800">{{ $ticket->reporter->phone ?: 'ไม่ได้ระบุ' }}</span>
                        </div>
                    </div>
                </div>

                @if (!Auth::user()->isAdmin() && $ticket->user_id === Auth::id() && $ticket->status === 'pending')
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5 shadow-sm">
                        <h3 class="flex items-center gap-2 font-bold text-rose-800">
                            <i class="fa-solid fa-ban"></i>
                            ยกเลิกรายการแจ้งซ่อม
                        </h3>
                        <p class="mt-2 text-xs leading-relaxed text-rose-700">
                            สามารถยกเลิกได้ก่อนที่เจ้าหน้าที่จะกดรับเรื่องและเริ่มดำเนินการเท่านั้น
                        </p>
                        <form method="POST" action="{{ route('tickets.cancel', $ticket->id) }}" class="mt-4" data-cancel-ticket>
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700">
                                <i class="fa-solid fa-trash-can"></i>
                                ยกเลิกรายการนี้
                            </button>
                        </form>
                    </div>
                @endif

                <!-- Status Management Panel for Admin / Technician -->
                @if (Auth::user()->role === 'admin')
                    <div
                        class="bg-gradient-to-br from-slate-900 to-indigo-950 rounded-2xl shadow-xl p-6 text-white space-y-4 border border-slate-800">
                        <h3 class="font-bold text-base flex items-center gap-2 text-blue-300">
                            <i class="fa-solid fa-user-shield"></i>
                            <span>การจัดการสำหรับช่าง / แอดมิน</span>
                        </h3>
                        <p class="text-xs text-slate-300">คุณสามารถเปลี่ยนสถานะงานซ่อมนี้เพื่อแจ้งความคืบหน้าแก่ผู้ใช้
                        </p>

                        <form action="{{ route('tickets.update-status', $ticket->id) }}" method="POST"
                            class="space-y-4">
                            @csrf
                            @method('PATCH')

                            <div>
                                <label for="status"
                                    class="flex items-center gap-2 text-xs font-semibold text-slate-300 mb-1.5 uppercase">
                                    <i class="fa-solid fa-pen-to-square text-blue-300"></i>
                                    อัปเดตสถานะงาน
                                </label>
                                <select id="status" name="status"
                                    class="w-full rounded-xl bg-slate-800 border-slate-700 text-white text-sm focus:border-blue-500 focus:ring-blue-500 py-2.5 px-3">
                                    <option value="pending" {{ $ticket->status === 'pending' ? 'selected' : '' }}>
                                        รอรับเรื่อง (Pending)
                                    </option>
                                    <option value="in_progress"
                                        {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>
                                        กำลังดำเนินการซ่อม (In Progress)
                                    </option>
                                    <option value="completed" {{ $ticket->status === 'completed' ? 'selected' : '' }}>
                                        ซ่อมเสร็จสิ้น (Completed)
                                    </option>
                                    <option value="cancelled" {{ $ticket->status === 'cancelled' ? 'selected' : '' }}>
                                        ยกเลิกรายการ (Cancelled)
                                    </option>
                                </select>
                            </div>

                            <button type="submit"
                                class="w-full py-2.5 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-xl text-sm shadow-md transition-colors flex items-center justify-center gap-2">
                                <i class="fa-solid fa-save"></i>
                                <span>บันทึกการอัปเดตสถานะ</span>
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const cancelForm = document.querySelector('[data-cancel-ticket]');

            if (!cancelForm) {
                return;
            }

            cancelForm.addEventListener('submit', function (event) {
                event.preventDefault();

                Swal.fire({
                    icon: 'warning',
                    title: 'ยืนยันการยกเลิก',
                    text: 'ต้องการยกเลิกรายการแจ้งซ่อมนี้จริงหรือไม่?',
                    showCancelButton: true,
                    confirmButtonText: 'ยืนยันยกเลิก',
                    cancelButtonText: 'ยังไม่ยกเลิก',
                    confirmButtonColor: '#e11d48',
                    cancelButtonColor: '#94a3b8'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        cancelForm.submit();
                    }
                });
            });
        });
    </script>
</x-app-layout>
