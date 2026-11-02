@props(['notifications', 'unreadCount', 'variant'])

@php
    $triggerClass = match ($variant) {
        'admin' => 'navigation-link ' . (request()->routeIs('notifications.*') ? 'is-active' : 'is-inactive'),
        'public' => 'public-navigation-link public-navigation-notification ' .
            (request()->routeIs('notifications.*') ? 'is-active' : 'is-inactive'),
        default => 'mobile-navigation-link notification-menu-mobile-trigger',
    };
@endphp

<div class="notification-menu notification-menu-{{ $variant }}" x-data="{ open: false }"
    @keydown.escape.window="open = false" @click.outside="open = false">
    <button type="button" class="{{ $triggerClass }}" @click="open = !open" :aria-expanded="open.toString()"
        aria-haspopup="true" aria-label="เปิดรายการแจ้งเตือน">
        <i class="fa-solid fa-bell {{ $variant === 'admin' || $variant === 'mobile' ? 'navigation-icon' : '' }}"></i>
        @if ($variant === 'admin')
            <span class="navigation-flex-fill">แจ้งเตือน</span>
        @elseif ($variant === 'mobile')
            <span>แจ้งเตือน</span>
        @endif
        @if ($unreadCount > 0)
            <span class="navigation-badge">
                {{ $variant === 'admin' ? ($unreadCount > 99 ? '99+' : $unreadCount) : ($unreadCount > 9 ? '9+' : $unreadCount) }}
            </span>
        @endif
    </button>

    <div x-show="open" x-cloak x-transition class="notification-dropdown-panel" role="region"
        aria-label="รายการแจ้งเตือน">
        <div class="notification-dropdown-header">
            <span>แจ้งเตือน</span>
            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="notification-dropdown-read-all">อ่านทั้งหมด</button>
                </form>
            @endif
        </div>

        @forelse ($notifications as $notification)
            <div class="notification-dropdown-entry">
                <form method="POST" action="{{ route('notifications.read', $notification) }}"
                    class="notification-dropdown-read-form">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                        class="notification-dropdown-item {{ $notification->is_read ? '' : 'is-unread' }}">
                        <span class="notification-dropdown-item-icon">
                            <i class="fa-solid fa-bell"></i>
                        </span>
                        <span class="notification-dropdown-item-content">
                            <span class="notification-dropdown-message">{{ $notification->display_message }}</span>
                            <span class="notification-dropdown-time">
                                {{ $notification->created_at->lt(now()->subDay())
                                    ? $notification->created_at->format('d/m/Y H:i น.')
                                    : $notification->created_at->diffForHumans() }}
                            </span>
                        </span>
                        @unless ($notification->is_read)
                            <span class="notification-dropdown-unread-dot" aria-label="ยังไม่ได้อ่าน"></span>
                        @endunless
                    </button>
                </form>
                <form method="POST" action="{{ route('notifications.destroy', $notification) }}" class="delete-form">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="notification-dropdown-delete btn-delete" aria-label="ลบการแจ้งเตือน"
                        title="ลบการแจ้งเตือน" data-message="{{ $notification->display_message }}">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </form>
            </div>
        @empty
            <p class="notification-dropdown-empty">ยังไม่มีแจ้งเตือน</p>
        @endforelse
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function (e) {
                const form = this.closest('form');
                const message = this.getAttribute('data-message');

                Swal.fire({
                    title: 'ต้องการลบการแจ้งเตือนนี้หรือไม่?',
                    text: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'ตกลง',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
</script>
