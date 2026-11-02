<x-app-layout title="แจ้งเตือน">
    <x-slot name="header">
        <div class="page-header">
            <div>
                <h2 class="page-title">
                    <span>แจ้งเตือน</span>
                </h2>
                <p class="page-description">
                    @if (Auth::user()->role === 'admin')
                        รายการแจ้งเตือนทั้งหมดจากทุกแผนกในระบบ (โหมดผู้ดูแลระบบ)
                    @else
                        รายการแจ้งเตือนทั้งหมดของคุณ
                    @endif
                </p>
            </div>
            @if ($notifications->where('is_read', false)->count() > 0)
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
                    <p class="notification-message">{{ $notification->display_message }}</p>
                    <p class="notification-time">
                        {{ $notification->created_at->lt(now()->subDay())
                            ? $notification->created_at->format('d/m/Y H:i น.')
                            : $notification->created_at->diffForHumans() }}
                    </p>
                </div>
                <div class="notification-actions">
                    <form method="POST" action="{{ route('notifications.read', $notification) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="table-link">
                            ดูรายการ
                        </button>
                    </form>
                    <form method="POST" action="{{ route('notifications.destroy', $notification) }}"
                        onsubmit="return confirm('ต้องการลบการแจ้งเตือนนี้หรือไม่?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="notification-delete-button">
                            <i class="fa-solid fa-trash-can"></i>
                            <span>ลบ</span>
                        </button>
                    </form>
                </div>
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
