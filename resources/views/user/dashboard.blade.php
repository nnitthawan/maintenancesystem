<x-app-layout>
    <x-slot name="header">
        <div class="page-header">
            <div>
                <h2 class="page-title flex items-center gap-3">
                    <span>สวัสดีคุณ <span class="nameUserLogin font-semibold">{{ Auth::user()->name }}</span> </span>
                </h2>
                <p class="page-description">
                    ติดตามความคืบหน้ารายการแจ้งซ่อมของคุณ
                </p>
            </div>
        </div>
    </x-slot>

    <div class="data-page-stack">

    </div>

</x-app-layout>
