@if (Auth::user()->isAdmin())
    <aside class="admin-sidebar" :class="{ 'is-open': sidebarOpen }">
        <div class="admin-sidebar-content sidebar-scroll">
            <div class="navigation-brand-row">
                <a href="{{ route('dashboard') }}" class="navigation-brand">
                    {{-- <div class="navigation-brand-icon"> --}}
                    <img src="{{ asset('favicon.ico') }}" alt="Logo" width="40" height="40">
                    {{-- </div> --}}
                    <div>
                        <h2 class="navigation-brand-title">ระบบแจ้งซ่อมครุภัณฑ์</h2>
                        <span class="navigation-brand-badge">
                            {{ Auth::user()->isAdmin() ? 'Admin Portal' : 'User Portal' }}
                        </span>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="navigation-close">
                    <i class="fa-solid fa-xmark navigation-icon-large"></i>
                </button>
            </div>

            <nav class="navigation-menu">
                <div class="navigation-section-title">
                    {{ Auth::user()->isAdmin() ? 'ภาพรวมระบบ' : 'เมนูของฉัน' }}
                </div>
                <a href="{{ route('dashboard') }}"
                    class="navigation-link {{ request()->routeIs('dashboard') ? 'is-active' : 'is-inactive' }}">
                    <i class="fa-solid fa-chart-pie navigation-icon"></i>
                    <span>แดชบอร์ด</span>
                </a>
                <a href="{{ route('tickets.index') }}"
                    class="navigation-link {{ request()->routeIs('tickets.index') || request()->routeIs('tickets.show') ? 'is-active' : 'is-inactive' }}">
                    <i class="fa-solid fa-list-check navigation-icon"></i>
                    <span>{{ Auth::user()->isAdmin() ? 'รายการแจ้งซ่อมทั้งหมด' : 'รายการแจ้งซ่อมของฉัน' }}</span>
                </a>
                <a href="{{ route('tickets.create') }}"
                    class="navigation-link {{ request()->routeIs('tickets.create') ? 'is-active' : 'is-inactive' }}">
                    <i
                        class="fa-solid fa-plus-circle navigation-icon navigation-create-icon {{ request()->routeIs('tickets.create') ? '' : 'is-muted' }}"></i>
                    <span>แจ้งซ่อมใหม่</span>
                </a>
                <a href="{{ route('notifications.index') }}"
                    class="navigation-link {{ request()->routeIs('notifications.*') ? 'is-active' : 'is-inactive' }}">
                    <i class="fa-solid fa-bell navigation-icon"></i>
                    <span class="navigation-flex-fill">แจ้งเตือน</span>
                    @php($unreadNotifications = Auth::user()->notifications()->where('is_read', false)->count())
                    @if ($unreadNotifications > 0)
                        <span
                            class="navigation-badge">{{ $unreadNotifications > 99 ? '99+' : $unreadNotifications }}</span>
                    @endif
                </a>

                @if (Auth::check() && Auth::user()->isAdmin())
                    <div class="navigation-section-title">จัดการระบบ</div>
                    <a href="{{ route('admin.categories.index') }}"
                        class="navigation-link {{ request()->routeIs('admin.categories.*') ? 'is-active' : 'is-inactive' }}">
                        <i class="fa-solid fa-layer-group navigation-icon"></i>
                        <span>ประเภทงานซ่อม</span>
                    </a>
                    <a href="{{ route('admin.departments.index') }}"
                        class="navigation-link {{ request()->routeIs('admin.departments.*') ? 'is-active' : 'is-inactive' }}">
                        <i class="fa-solid fa-building navigation-icon"></i>
                        <span>กลุ่มงาน / แผนก</span>
                    </a>
                    <div class="navigation-section-title">รายงาน / บัญชีผู้ใช้</div>
                    <a href="{{ route('admin.reports.index') }}"
                        class="navigation-link {{ request()->routeIs('admin.reports.*') ? 'is-active' : 'is-inactive' }}">
                        <i class="fa-solid fa-chart-column navigation-icon"></i>
                        <span>รายงาน / สรุปยอด</span>
                    </a>
                    <a href="{{ route('admin.users.index') }}"
                        class="navigation-link {{ request()->routeIs('admin.users.*') ? 'is-active' : 'is-inactive' }}">
                        <i class="fa-solid fa-users-gear navigation-icon"></i>
                        <span>จัดการบัญชีผู้ใช้</span>
                    </a>
                @endif
            </nav>
        </div>

        <div class="admin-navigation-footer">
            <a href="{{ Auth::user()->isAdmin() ? route('profile.edit') : '#' }}"
                class="navigation-user-card navigation-footer-link {{ request()->routeIs('profile.*') ? 'is-active' : '' }}">
                <div class="navigation-user-card-content">
                    <div class="navigation-avatar">
                        {{ mb_substr(Auth::user()->name ?? 'U', 0, 1) }}
                    </div>
                    <div class="navigation-user-details">
                        <p class="navigation-user-name">{{ Auth::user()->name ?? 'ผู้ใช้งาน' }}</p>
                        <p class="navigation-user-department">
                            {{ Auth::user()->department->department_name ?? (Auth::user()->isAdmin() ? 'ผู้ดูแลระบบ' : 'ไม่มีสังกัด') }}
                        </p>
                    </div>
                </div>
            </a>
            <div class="navigation-footer-menu">
                {{-- <a href="{{ route('profile.edit') }}" class="navigation-link navigation-footer-link {{ request()->routeIs('profile.*') ? 'is-active' : '' }}">
                <i class="fa-solid fa-user-gear"></i>
                <span>แก้ไขข้อมูลส่วนตัว</span>
            </a> --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="navigation-link navigation-logout-link">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>ออกจากระบบ</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>
@else
    <nav class="public-navigation" x-data="{ userMenuOpen: false }">
        <div class="public-navigation-inner">
            <a href="{{ route('dashboard') }}" class="public-navigation-brand">
                <span class="public-navigation-logo">
                    <img src="{{ asset('favicon.ico') }}" alt="Logo" width="40" height="40">
                </span>
                <span class="public-navigation-title">ระบบแจ้งซ่อมครุภัณฑ์</span>
            </a>

            <div class="public-navigation-links">
                <a href="{{ route('dashboard') }}"
                    class="public-navigation-link {{ request()->routeIs('dashboard') ? 'is-active' : 'is-inactive' }}">
                    <i class="fa-solid fa-house"></i>
                    <span class="public-navigation-label">หน้าหลัก</span>
                </a>
                <a href="{{ route('tickets.index') }}"
                    class="public-navigation-link {{ request()->routeIs('tickets.index') || request()->routeIs('tickets.show') ? 'is-active' : 'is-inactive' }}">
                    <i class="fa-solid fa-list-check"></i>
                    <span class="public-navigation-label">รายการแจ้งซ่อมของฉัน</span>
                </a>
                <a href="{{ route('notifications.index') }}"
                    class="public-navigation-link public-navigation-notification {{ request()->routeIs('notifications.*') ? 'is-active' : 'is-inactive' }}">
                    <i class="fa-solid fa-bell"></i>
                    <span class="public-navigation-label">แจ้งเตือน</span>
                    @php($unreadNotifications = Auth::user()->notifications()->where('is_read', false)->count())
                    @if ($unreadNotifications > 0)
                        <span
                            class="navigation-badge">{{ $unreadNotifications > 99 ? '99+' : $unreadNotifications }}</span>
                    @endif
                </a>
                <a href="{{ route('tickets.create') }}"
                    class="public-navigation-link mobile-navigation-link is-primary">
                    <i class="fa-solid fa-plus"></i>
                    <span class="public-navigation-label">แจ้งซ่อม</span>
                </a>
            </div>

            <div class="public-navigation-user">
                <div class="public-navigation-user-info">
                    <p class="public-navigation-user-name">{{ Auth::user()->name }}</p>
                    <p class="public-navigation-user-department">
                        {{ Auth::user()->department->department_name ?? 'ไม่มีสังกัด' }}</p>
                </div>
                <a href="{{ route('profile.edit') }}"
                    class="public-navigation-icon-button public-navigation-account-button" title="แก้ไขข้อมูลส่วนตัว">
                    <i class="fa-solid fa-user"></i>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="public-navigation-icon-button public-navigation-account-button"
                        title="ออกจากระบบ">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                </form>
                <button type="button" @click="userMenuOpen = !userMenuOpen" class="mobile-navigation-toggle"
                    aria-label="เปิดเมนู">
                    <i class="fa-solid fa-bars navigation-icon-large"></i>
                </button>
            </div>
        </div>

        <div x-show="userMenuOpen" x-cloak class="mobile-navigation-overlay" @click="userMenuOpen = false"></div>

        <aside x-show="userMenuOpen" x-cloak x-transition:enter="transform transition ease-out duration-300"
            x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in duration-200" x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full" class="mobile-navigation-drawer">
            <div class="navigation-drawer-header">
                <div>
                    <p class="navigation-drawer-title">เมนูผู้ใช้งาน</p>
                    <p class="navigation-drawer-user">{{ Auth::user()->name }}</p>
                </div>
                <button type="button" @click="userMenuOpen = false" class="public-navigation-icon-button"
                    aria-label="ปิดเมนู">
                    <i class="fa-solid fa-xmark navigation-icon-large"></i>
                </button>
            </div>

            <div class="mobile-navigation-menu">
                <a href="{{ route('dashboard') }}" class="mobile-navigation-link">
                    <i class="fa-solid fa-house navigation-icon"></i>หน้าหลัก
                </a>
                <a href="{{ route('tickets.index') }}" class="mobile-navigation-link">
                    <i class="fa-solid fa-list-check navigation-icon"></i>รายการแจ้งซ่อมของฉัน
                </a>
                <a href="{{ route('notifications.index') }}" class="mobile-navigation-link">
                    <i class="fa-solid fa-bell navigation-icon"></i>แจ้งเตือน
                </a>
                <a href="{{ route('tickets.create') }}" class="mobile-navigation-link is-primary">
                    <i class="fa-solid fa-plus navigation-icon"></i>แจ้งซ่อมใหม่
                </a>
                <a href="{{ route('profile.edit') }}" class="mobile-navigation-link">
                    <i class="fa-solid fa-user navigation-icon"></i>โปรไฟล์
                </a>
            </div>

            <div class="mobile-navigation-footer">
                <p class="navigation-user-department">{{ Auth::user()->department->department_name ?? 'ไม่มีสังกัด' }}
                </p>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="navigation-logout-link mobile-navigation-link">
                        <i class="fa-solid fa-right-from-bracket navigation-icon"></i>ออกจากระบบ
                    </button>
                </form>
            </div>
            </div>
    </nav>
@endif
