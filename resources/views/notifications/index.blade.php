<x-app-layout title="แจ้งเตือน">
    <x-slot name="header">
        <div class="page-header">
            <div>
            <h2 class="page-title">
                    {{-- <i class="fa-solid fa-bell text-blue-600"></i> --}}
                    <span>แจ้งเตือน</span>
                </h2>
                <p class="page-description">
                    @if(Auth::user()->role === 'admin')
                        รายการแจ้งเตือนทั้งหมดจากทุกแผนกในระบบ (โหมดผู้ดูแลระบบ)
                    @else
                        รายการแจ้งเตือนทั้งหมดของคุณ
                    @endif
                </p>
            </div>
            @if($notifications->where('is_read', false)->count() > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="table-link">
                        ทำเครื่องหมายว่าอ่านทั้งหมด
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="notification-list">
        @forelse($notifications as $notification)
            <div class="notification-item {{ $notification->is_read ? '' : 'is-unread' }}">
                <div class="notification-icon">
                        <i class="fa-solid fa-bell"></i>
                </div>
                <div class="notification-content">
                    @php
                        $notificationMessage = str_replace(
                            ['เปลี่ยนสถานะเป็น pending', 'เปลี่ยนสถานะเป็น in_progress', 'เปลี่ยนสถานะเป็น completed', 'เปลี่ยนสถานะเป็น cancelled'],
                            ['เปลี่ยนสถานะเป็น รอรับเรื่อง', 'เปลี่ยนสถานะเป็น กำลังดำเนินการ', 'เปลี่ยนสถานะเป็น เสร็จสิ้น', 'เปลี่ยนสถานะเป็น ยกเลิก'],
                            $notification->message,
                        );
                    @endphp
                    <p class="notification-message">{{ $notificationMessage }}</p>
                    <p class="notification-time">{{ $notification->created_at->diffForHumans() }}</p>
                </div>
                    <form method="POST" action="{{ route('notifications.read', $notification) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="table-link">
                            ดูรายการ
                        </button>
                    </form>
            </div>
        @empty
            <div class="data-card empty-state">
                <i class="fa-regular fa-bell-slash text-3xl mb-3"></i>
                <p>ยังไม่มีแจ้งเตือน</p>
            </div>
        @endforelse

        <div class="pagination-wrap">{{ $notifications->links() }}</div>
    </div>
</x-app-layout>