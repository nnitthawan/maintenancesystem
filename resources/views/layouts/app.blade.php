@props(['title' => null])

@php
    $pageTitle = $title ?? match (true) {
        request()->routeIs('dashboard') => Auth::user()?->isAdmin() ? 'แดชบอร์ด' : 'หน้าหลัก',
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
    <link rel="stylesheet" href="{{ asset('css/navigation.css') }}?v={{ filemtime(public_path('css/navigation.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/pages.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ticket.css') }}">

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

<body class="font-sans antialiased bg-slate-100 text-slate-800 min-h-screen"
    x-data="{ sidebarOpen: false, ticketModalOpen: {{ old('_ticket_modal') ? 'true' : 'false' }}, ticketReviewOpen: false }"
    @keydown.escape.window="ticketReviewOpen ? ticketReviewOpen = false : ticketModalOpen = false">

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
                <div class="py-4 shrink-0">
                    <div class="public-navigation-header w-full {{ Auth::user()->isAdmin()}}">
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
            <div class="{{ Auth::user()->isAdmin() ? 'min-h-0 flex-1 overflow-y-auto' : '' }} pb-16 sm:pb-16 lg:pb-16 ">
                <div class="{{ Auth::user()->isAdmin() ? 'listMaintenanceSystems' : '' }}">
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

    <div x-cloak x-show="ticketModalOpen" x-transition.opacity class="ticket-create-modal"
        @ticket-review-open="ticketReviewOpen = true"
        x-effect="document.body.classList.toggle('overflow-hidden', ticketModalOpen)"
        role="presentation">
        <section class="ticket-create-modal-panel" role="dialog" aria-modal="true"
            aria-labelledby="ticketCreateModalTitle">
            <header class="ticket-create-modal-header">
                <div>
                    <h2 id="ticketCreateModalTitle">แจ้งซ่อมครุภัณฑ์</h2>
                    <p>กรอกรายละเอียดอุปกรณ์หรือสถานที่ที่ต้องการแจ้งซ่อม</p>
                </div>
                <button type="button" class="ticket-create-modal-close"
                    @click="ticketModalOpen = false" aria-label="ปิดหน้าต่างแจ้งซ่อม">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </header>

            <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data"
                class="ticket-create-modal-form">
                @csrf
                <input type="hidden" name="_ticket_modal" value="1">

                <div class="ticket-create-modal-reporter">
                    <i class="fa-solid fa-user-check"></i>
                    <span>{{ Auth::user()->name }}</span>
                    <span>{{ Auth::user()->department->department_name ?? 'ไม่มีสังกัด' }}</span>
                </div>

                @if ($errors->any() && old('_ticket_modal'))
                    <div class="ticket-create-modal-errors" role="alert">
                        <p>กรุณาตรวจสอบข้อมูลก่อนส่ง:</p>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="ticket-create-modal-fields">
                    <div>
                        <label for="modal_category_id">หมวดหมู่การซ่อม <span class="ticket-required">*</span></label>
                        <select id="modal_category_id" name="category_id" required>
                            <option value="">-- เลือกหมวดหมู่ปัญหา --</option>
                            @foreach ($ticketCategories as $category)
                                <option value="{{ $category->id }}"
                                    @selected(old('category_id') == $category->id)>
                                    {{ $category->category_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            @if (old('_ticket_modal'))
                                <p class="ticket-create-modal-field-error">{{ $message }}</p>
                            @endif
                        @enderror
                    </div>
                    <div>
                        <label for="modal_asset_no">รหัสครุภัณฑ์ / เลขทะเบียนอุปกรณ์ <span>(ถ้ามี)</span></label>
                        <input id="modal_asset_no" type="text" name="asset_no" maxlength="100"
                            value="{{ old('asset_no') }}" placeholder="เช่น EQ-67-0012 หรือ IT-PC-05">
                        @error('asset_no')
                            @if (old('_ticket_modal'))
                                <p class="ticket-create-modal-field-error">{{ $message }}</p>
                            @endif
                        @enderror
                    </div>
                    <div class="ticket-create-modal-field-full">
                        <label for="modal_location">สถานที่ / อาคาร / ชั้น / ห้อง <span class="ticket-required">*</span></label>
                        <input id="modal_location" type="text" name="location" required maxlength="255"
                            value="{{ old('location') }}" placeholder="เช่น อาคารอำนวยการ ชั้น 2 ห้องประชุมใหญ่ 1">
                        @error('location')
                            @if (old('_ticket_modal'))
                                <p class="ticket-create-modal-field-error">{{ $message }}</p>
                            @endif
                        @enderror
                    </div>
                    <div class="ticket-create-modal-field-full">
                        <label for="modal_symptom">รายละเอียดอาการเสีย / ปัญหาที่พบ <span class="ticket-required">*</span></label>
                        <textarea id="modal_symptom" name="symptom" rows="4" required
                            placeholder="อธิบายอาการชำรุด หรือปัญหาที่เกิดขึ้นโดยละเอียด...">{{ old('symptom') }}</textarea>
                        @error('symptom')
                            @if (old('_ticket_modal'))
                                <p class="ticket-create-modal-field-error">{{ $message }}</p>
                            @endif
                        @enderror
                    </div>
                    <div class="ticket-create-modal-field-full">
                        <label for="modal_images">แนบรูปภาพประกอบ <span>(สูงสุด 5 รูป, ไม่เกิน 5MB ต่อรูป)</span></label>
                        <input id="modal_images" type="file" name="images[]" multiple accept="image/*">
                        <p id="modal-image-error" class="ticket-create-modal-field-error" role="alert" hidden></p>
                        <div id="modal-image-preview" class="ticket-create-modal-image-preview" aria-live="polite"></div>
                    </div>
                </div>

                <footer class="ticket-create-modal-actions">
                    <button type="button" class="ticket-create-modal-cancel"
                        @click="ticketModalOpen = false">ยกเลิก</button>
                    <button type="submit" class="ticket-create-modal-submit">
                        <i class="fa-solid fa-paper-plane"></i>
                        ตรวจสอบข้อมูล
                    </button>
                </footer>
            </form>
        </section>
    </div>

    <div x-cloak x-show="ticketReviewOpen" x-transition.opacity class="ticket-create-modal ticket-review-modal"
        @click.self="ticketReviewOpen = false" role="presentation">
        <section class="ticket-create-modal-panel ticket-review-panel" role="dialog" aria-modal="true"
            aria-labelledby="ticketReviewTitle">
            <header class="ticket-create-modal-header">
                <div>
                    <h2 id="ticketReviewTitle">ตรวจสอบข้อมูลก่อนส่ง</h2>
                    <p>โปรดตรวจสอบรายละเอียดและรูปภาพก่อนยืนยันการแจ้งซ่อม</p>
                </div>
                <button type="button" class="ticket-create-modal-close"
                    @click="ticketReviewOpen = false" aria-label="กลับไปแก้ไข">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </header>
            <div class="ticket-create-modal-form">
                <dl class="ticket-review-details">
                    <div><dt>หมวดหมู่การซ่อม</dt><dd id="review-category">-</dd></div>
                    <div><dt>รหัสครุภัณฑ์ / เลขทะเบียน</dt><dd id="review-asset">-</dd></div>
                    <div class="ticket-review-detail-full"><dt>สถานที่ / อาคาร / ชั้น / ห้อง</dt><dd id="review-location">-</dd></div>
                    <div class="ticket-review-detail-full"><dt>รายละเอียดอาการเสีย / ปัญหาที่พบ</dt><dd id="review-symptom">-</dd></div>
                </dl>
                <div class="ticket-review-images-wrap">
                    <h3>รูปภาพประกอบ <span id="review-image-count"></span></h3>
                    <div id="review-images" class="ticket-create-modal-image-preview"></div>
                </div>
                <footer class="ticket-create-modal-actions">
                    <button type="button" class="ticket-create-modal-cancel"
                        @click="ticketReviewOpen = false">กลับไปแก้ไข</button>
                    <button type="button" id="ticket-review-confirm" class="ticket-create-modal-submit">
                        <i class="fa-solid fa-paper-plane"></i>
                        ยืนยันและส่งแจ้งซ่อม
                    </button>
                </footer>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('.ticket-create-modal-form');
            const imageInput = document.getElementById('modal_images');
            const previewGrid = document.getElementById('modal-image-preview');
            const imageError = document.getElementById('modal-image-error');
            const reviewImages = document.getElementById('review-images');
            const reviewConfirm = document.getElementById('ticket-review-confirm');
            const reviewModal = document.querySelector('.ticket-review-modal');
            const ticketModal = document.querySelector('.ticket-create-modal:not(.ticket-review-modal)');
            let selectedImages = [];
            let confirmed = false;

            if (!form || !imageInput || !previewGrid || !reviewConfirm || !reviewModal || !ticketModal) {
                return;
            }

            function setImageError(message) {
                imageError.textContent = message;
                imageError.hidden = !message;
            }

            function renderPreviews(files, container, allowRemove) {
                container.replaceChildren();
                if (files.length === 0) {
                    const empty = document.createElement('p');
                    empty.className = 'ticket-review-no-images';
                    empty.textContent = 'ไม่ได้แนบรูปภาพ';
                    container.appendChild(empty);
                    return;
                }

                Array.from(files).forEach(function (file, index) {
                    const item = document.createElement('div');
                    item.className = 'ticket-create-modal-image-item';
                    const image = document.createElement('img');
                    image.alt = file.name;
                    image.src = URL.createObjectURL(file);
                    image.addEventListener('load', function () {
                        URL.revokeObjectURL(image.src);
                    }, { once: true });
                    item.appendChild(image);

                    const name = document.createElement('span');
                    name.textContent = file.name;
                    item.appendChild(name);

                    if (allowRemove) {
                        const remove = document.createElement('button');
                        remove.type = 'button';
                        remove.className = 'ticket-create-modal-image-remove';
                        remove.setAttribute('aria-label', 'ลบรูปภาพ ' + file.name);
                        remove.innerHTML = '<i class="fa-solid fa-xmark"></i>';
                        remove.addEventListener('click', function () {
                            const transfer = new DataTransfer();
                            selectedImages = selectedImages.filter(function (selectedFile, selectedIndex) {
                                if (selectedIndex !== index) {
                                    return true;
                                }
                                return false;
                            });
                            selectedImages.forEach(function (selectedFile) {
                                transfer.items.add(selectedFile);
                            });
                            imageInput.files = transfer.files;
                            setImageError('');
                            renderPreviews(imageInput.files, previewGrid, true);
                        });
                        item.appendChild(remove);
                    }

                    container.appendChild(item);
                });
            }

            imageInput.addEventListener('change', function () {
                const newFiles = Array.from(imageInput.files);
                const validFiles = newFiles.filter(function (file) {
                    return file.type.startsWith('image/') && file.size <= 5 * 1024 * 1024;
                });
                const issues = [];
                if (selectedImages.length + validFiles.length > 5) {
                    issues.push('แนบรูปภาพได้สูงสุด 5 รูป รูปที่เกินจะไม่ถูกเพิ่ม');
                }
                if (validFiles.length !== newFiles.length) {
                    issues.push('เลือกได้เฉพาะไฟล์รูปภาพที่มีขนาดไม่เกิน 5MB ต่อรูป');
                }

                const transfer = new DataTransfer();
                selectedImages.concat(validFiles).slice(0, 5).forEach(function (file) {
                    transfer.items.add(file);
                });
                selectedImages = Array.from(transfer.files);
                imageInput.files = transfer.files;
                setImageError(issues.join(' และ '));
                renderPreviews(imageInput.files, previewGrid, true);
            });

            function fillReview() {
                const category = document.getElementById('modal_category_id');
                document.getElementById('review-category').textContent =
                    category.options[category.selectedIndex]?.text?.trim() || '-';
                document.getElementById('review-asset').textContent =
                    document.getElementById('modal_asset_no').value.trim() || '-';
                document.getElementById('review-location').textContent =
                    document.getElementById('modal_location').value.trim() || '-';
                document.getElementById('review-symptom').textContent =
                    document.getElementById('modal_symptom').value.trim() || '-';
                document.getElementById('review-image-count').textContent =
                    '(' + imageInput.files.length + ' รูป)';
                renderPreviews(imageInput.files, reviewImages, false);
            }

            form.addEventListener('submit', function (event) {
                if (confirmed) {
                    return;
                }

                event.preventDefault();
                if (!form.reportValidity()) {
                    return;
                }
                if (imageError.textContent) {
                    imageInput.focus();
                    return;
                }

                fillReview();
                ticketModal.dispatchEvent(new CustomEvent('ticket-review-open', { bubbles: true }));
            });

            reviewConfirm.addEventListener('click', function () {
                confirmed = true;
                reviewConfirm.disabled = true;
                reviewConfirm.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> กำลังส่งข้อมูล...';
                form.requestSubmit();
            });
        });
    </script>

</body>
</html>