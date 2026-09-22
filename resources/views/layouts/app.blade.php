@props(['title' => null])

@php
    $pageTitle = $title ?? match (true) {
        request()->routeIs('dashboard') => 'แดชบอร์ด',
        request()->routeIs('tickets.index') => 'รายการแจ้งซ่อม',
        request()->routeIs('tickets.create') => 'แจ้งซ่อมใหม่',
        request()->routeIs('tickets.show') => 'รายละเอียดการแจ้งซ่อม',
        request()->routeIs('admin.categories.*') => 'ประเภทงานซ่อม',
        request()->routeIs('admin.departments.*') => 'กลุ่มงาน / แผนก',
        request()->routeIs('profile.*') => 'ข้อมูลส่วนตัว',
        default => 'Maintenance System',
    };
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $pageTitle }} | Maintenance System</title>

    <link rel="stylesheet" href="{{ asset('css/lineseed.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app-layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/navigation.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages.css') }}">

    <!-- Font Awesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @if(request()->routeIs('profile.*'))
        <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
    @endif

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['LINESeedSansTH', 'sans-serif'],
                    },
                    colors: {
                        navy: {
                            800: '#1E2A44',
                            900: '#0F172A',
                            950: '#090D16',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body class="font-sans antialiased bg-slate-100 text-slate-800 min-h-screen" x-data="{ sidebarOpen: false }">

    @if(Auth::user()->isAdmin())
    <!-- Mobile admin navigation -->
    <header class="flex items-center justify-between bg-slate-900 px-4 py-3 text-white sm:hidden border-b border-slate-800 sticky top-0 z-40">
        <button type="button" @click="sidebarOpen = true" class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 focus:outline-none" aria-label="เปิดเมนู">
            <i class="fa-solid fa-bars text-xl"></i>
        </button>
        <div class="flex items-center gap-2">
            @if(Auth::check() && Auth::user()->isAdmin())
                <span class="px-2 py-0.5 text-xs font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30 rounded-full">ADMIN</span>
            @endif
            <span class="font-bold text-sm tracking-wide">Maintenance System</span>
        </div>
    </header>

    <!-- Sidebar Backdrop (Mobile) -->
    <div x-show="sidebarOpen" 
         @click="sidebarOpen = false" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 sm:hidden"></div>
    @endif

    <div class="{{ Auth::user()->isAdmin() ? 'min-h-screen flex' : '' }}">

        @include('layouts.navigation')

        <!-- Main Content Area -->
        <main class="flex min-w-0 flex-col justify-start overflow-hidden {{ Auth::user()->isAdmin() ? 'min-h-0 flex-1 sm:ml-64' : '' }}">
            
            <!-- Page Header Slot (ถ้ามีการใช้งาน $header) -->
            @isset($header)
                <div class="bg-white border-b border-slate-200 py-4 shrink-0 shadow-sm">
                    <div class="w-full {{ Auth::user()->isAdmin() ? 'px-4 sm:px-6 lg:px-8' : 'mx-auto max-w-7xl px-4 sm:px-6 lg:px-8' }}">
                        {{ $header }}
                    </div>
                </div>
            @endisset

            <!-- Session Alert Messages (SweetAlert2) -->
            @if (session('success'))
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'สำเร็จ!',
                            text: "{{ session('success') }}",
                            timer: 3500,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                    });
                </script>
            @endif

            @if (session('error'))
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: "{{ session('error') }}",
                            confirmButtonColor: '#2563eb'
                        });
                    });
                </script>
            @endif

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    document.querySelectorAll('form[action*="/logout"]').forEach(function (form) {
                        form.addEventListener('submit', function (event) {
                            event.preventDefault();

                            Swal.fire({
                                icon: 'warning',
                                title: 'ยืนยันการออกจากระบบ',
                                text: 'คุณต้องการออกจากระบบจริงใช่ไหม?',
                                showCancelButton: true,
                                confirmButtonText: 'ออกจากระบบ',
                                cancelButtonText: 'ยกเลิก',
                                confirmButtonColor: '#e11d48',
                                cancelButtonColor: '#94a3b8'
                            }).then(function (result) {
                                if (result.isConfirmed) {
                                    form.submit();
                                }
                            });
                        });
                    });
                });
            </script>

            <!-- Main Scrollable Content -->
            <div class="{{ Auth::user()->isAdmin() ? 'min-h-0 flex-1 overflow-y-auto' : '' }} pb-16 pt-3 sm:pb-16 sm:pt-4 lg:pb-16 lg:pt-5">
                <div class="w-full {{ Auth::user()->isAdmin() ? 'px-4 sm:px-6 lg:px-8' : 'mx-auto max-w-7xl px-4 sm:px-6 lg:px-8' }}">
                    {{ $slot }}
                </div>
            </div>

            @if(Auth::user()->isAdmin())
                <footer class="bg-white border-t border-slate-200 py-3 text-center text-xs text-slate-400 shrink-0">
                    &copy; {{ date('Y') }} Maintenance System &bull; ระบบแจ้งซ่อมครุภัณฑ์
                </footer>
            @endif

        </main>

        @if(!Auth::user()->isAdmin())
            <footer class="fixed bottom-0 left-0 right-0 z-30 bg-white border-t border-slate-200 py-3 text-center text-xs text-slate-400">
                &copy; {{ date('Y') }} Maintenance System &bull; ระบบแจ้งซ่อมครุภัณฑ์
            </footer>
        @endif

    </div>

</body>
</html>