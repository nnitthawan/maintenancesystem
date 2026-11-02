<x-app-layout>
    <x-slot name="header">
        <div class="page-header">
            <div>
                <h2 class="page-title">
                    <span>แบบฟอร์มแจ้งซ่อม</span>
                </h2>
                <p class="page-description">กรอกรายละเอียดอุปกรณ์หรือสถานที่ที่ต้องการแจ้งซ่อมบำรุง</p>
            </div>
        </div>
    </x-slot>

    <div class="data-page-stack">
        <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data" class="ticket-form"
            id="ticketForm">
            @csrf
            <div class="ticket-reporter-card">
                <div class="flex items-center gap-3">
                    <div
                        class="w-12 h-12 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-lg shadow-md">
                        {{ mb_substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800">
                            {{ Auth::user()->name }}
                        </h4>
                        <p class="text-xs text-slate-500">
                            เบอร์โทร:<span
                                class="font-medium text-slate-700">{{ Auth::user()->phone ?: 'ไม่ได้ระบุ' }}</span>
                            |สังกัด
                            <span
                                class="font-medium text-slate-700">{{ Auth::user()->department->department_name ?? 'ไม่ระบุ' }}</span>
                        </p>
                    </div>
                </div>
                <span class="px-3 py-1 bg-blue-600 text-white rounded-full text-xs font-semibold">
                    <i class="fa-solid fa-user-check me-1"></i>
                    ผู้แจ้งซ่อม
                </span>
            </div>

            <div class="data-card ticket-form-card">
                @if ($errors->any())
                    <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-sm mb-6">
                        <div class="font-semibold flex items-center gap-2 mb-1">
                            <span>
                                กรุณาตรวจสอบข้อมูลก่อนส่ง:
                            </span>
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-xs">
                            @foreach ($errors->all() as $error)
                                <li>
                                    {{ $error }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label for="category_id"
                                class="block text-sm font-semibold text-slate-700 mb-2">หมวดหมู่การซ่อม<span
                                    class="text-rose-500">*</span>
                            </label>
                            <select id="category_id" name="category_id" required
                                class="inputCreatePage">
                                <option value="" disabled {{ old('category_id') ? '' : 'selected' }}>
                                    -- เลือกหมวดหมู่ปัญหา --
                                </option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->category_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }} </p>
                            @enderror
                        </div>
                        <div>
                            <label for="asset_no" class="block text-sm font-semibold text-slate-700 mb-2">
                                รหัสครุภัณฑ์ / เลขทะเบียนอุปกรณ์
                                <span class="text-slate-400 font-normal">(ถ้ามี) </span>
                            </label>
                            <input type="text" id="asset_no" name="asset_no" value="{{ old('asset_no') }}"
                                placeholder="เช่น EQ-67-0012 หรือ IT-PC-05"
                                 class="inputCreatePage">
                            @error('asset_no')
                                <p class="text-xs text-rose-500 mt-1">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                    <div>
                        <label for="location" class="block text-sm font-semibold text-slate-700 mb-2">
                            สถานที่ / อาคาร / ชั้น / ห้อง <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="location" name="location" value="{{ old('location') }}" required
                            placeholder="เช่น อาคารอำนวยการ ชั้น 2 ห้องประชุมใหญ่ 1"
                            class="inputCreatePage">
                        @error('location')
                            <p class="text-xs text-rose-500 mt-1">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                    <div>
                        <label for="symptom"class="block text-sm font-semibold text-slate-700 mb-2">
                            รายละเอียดอาการเสีย / ปัญหาที่พบ <span class="text-rose-500">*</span>
                        </label>
                        <textarea id="symptom" name="symptom" rows="4" required
                            placeholder="อธิบายอาการชำรุด หรือปัญหาที่เกิดขึ้นโดยละเอียด..."
                            class="inputCreatePage">{{ old('symptom') }}</textarea>
                        @error('symptom')
                            <p class="text-xs text-rose-500 mt-1">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            แนบรูปภาพประกอบ
                            <span class="text-slate-400 font-normal">
                                (อัปโหลดได้หลายรูป, ไม่เกิน 5MB)
                            </span>
                        </label>
                        <label class="inputImageCreatePage block">
                            <input type="file" id="images" name="images[]" multiple accept="image/*"
                                class="hidden" onchange="previewImages(event)">
                            <div
                                class="w-14 h-14 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mx-auto mb-3 text-xl group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                            </div>
                            <p class="text-sm font-medium text-slate-700">
                                คลิกที่นี่ หรือ ลากรูปภาพมาวาง
                            </p>
                            <p class="text-xs text-slate-400 mt-1">
                                สามารถเลือกได้สูงสุด 5 รูป
                            </p>
                        </label>
                        <div id="image-preview-grid" class="grid grid-cols-2 sm:grid-cols-5 gap-4 mt-4 hidden">
                        </div>
                    </div>
                    <div class="form-actions ticket-form-actions">
                        <a href="{{ route('tickets.index') }}" class="btCancelCreatePage" data-cancel-createTicket>
                            ยกเลิก
                        </a>
                        <button type="submit" class="btSubmitCreatePage" data-submit-ticket>
                            <i class="fa-solid fa-paper-plane"></i>
                            <span>
                                ส่งแจ้งซ่อม
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- ฟอร์มตรวจสอบก่อนยืนยัน --}}
    <div id="ticketConfirmModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4">
        <div id="ticketModalBackdrop" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm">
        </div>

        <div id="ticketModalContent"
            class="relative w-full max-w-3xl max-h-[90vh] overflow-y-auto bg-white rounded-2xl shadow-2xl">
            <div
                class="sticky top-0 z-10 bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">ตรวจสอบข้อมูลก่อนส่ง</h3>
                    <p class="text-xs text-slate-500 mt-1">
                        กรุณาตรวจสอบข้อมูลให้ถูกต้องก่อนยืนยันการแจ้งซ่อม
                    </p>
                </div>
                <button type="button" id="closeTicketModal"
                    class="w-9 h-9 rounded-full hover:bg-slate-100 text-slate-500 hover:text-slate-700 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-4">
                        <p class="text-xs text-slate-500 mb-1"> หมวดหมู่การซ่อม</p>
                        <p id="confirmCategory" class="font-semibold text-slate-800"> - </p>
                    </div>

                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-4">
                        <p class="text-xs text-slate-500 mb-1">รหัสครุภัณฑ์ / เลขทะเบียน</p>
                        <p id="confirmAsset" class="font-semibold text-slate-800"> - </p>
                    </div>

                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-4 md:col-span-2">

                        <p class="text-xs text-slate-500 mb-1">
                            สถานที่ / อาคาร / ชั้น / ห้อง
                        </p>
                        <p id="confirmLocation" class="font-semibold text-slate-800"> - </p>
                    </div>

                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-4 md:col-span-2">
                        <p class="text-xs text-slate-500 mb-2"> รายละเอียดอาการเสีย / ปัญหาที่พบ</p>
                        <div id="confirmSymptom" class="text-sm text-slate-700 whitespace-pre-wrap leading-relaxed">
                            -
                        </div>
                    </div>
                </div>

                <div class="mt-5">
                    <div class="flex items-center gap-2 mb-3">
                        <i class="fa-solid fa-images text-blue-600"></i>
                        <span class="font-semibold text-slate-700">
                            รูปภาพประกอบ
                        </span>
                        <span id="confirmImageCount" class="text-xs text-slate-400">
                        </span>
                    </div>

                    <div id="confirmImages" class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 gap-3">
                    </div>
                </div>


                <!-- Notice -->
                <div class="mt-5 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm">
                    <div class="flex gap-3">
                        <i class="fa-solid fa-circle-info mt-0.5"></i>
                        <div>
                            <p class="font-semibold mb-1">
                                ตรวจสอบข้อมูลอีกครั้ง
                            </p>
                            <p class="text-xs leading-relaxed">
                                หากข้อมูลถูกต้อง ให้กด
                                <strong>ยืนยันการแจ้งซ่อม</strong>
                                หากพบข้อมูลผิด ให้กด
                                <strong>กลับไปแก้ไข</strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="sticky bottom-0 bg-white border-t border-slate-200 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                <button type="button" id="editTicketButton"
                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-medium transition">
                    <i class="fa-solid fa-pen mr-1"></i>
                    กลับไปแก้ไข
                </button>

                <button type="button" id="confirmTicketButton"
                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow-sm transition">
                    <i class="fa-solid fa-paper-plane mr-1"></i>
                    ยืนยันการแจ้งซ่อม
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const maxImageCount = 5;
        const selectedImageFiles = new DataTransfer();

        function previewImages(event) {
            addImageFiles(event.target.files);
        }

        function addImageFiles(files) {
            let hasExceededLimit = false;
            Array.from(files).forEach(function(file) {
                if (
                    file.type.startsWith('image/') &&
                    selectedImageFiles.files.length < maxImageCount
                ) {

                    selectedImageFiles.items.add(file);

                } else if (file.type.startsWith('image/')) {

                    hasExceededLimit = true;

                }
            });

            if (hasExceededLimit) {

                Swal.fire({
                    icon: 'warning',
                    title: 'แนบรูปภาพได้สูงสุด 5 รูป',
                    text: 'รูปภาพที่เกินจากจำนวนที่กำหนดจะไม่ถูกเพิ่ม',
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#2563eb'
                });
            }
            document.getElementById('images').files = selectedImageFiles.files;
            renderImagePreviews();
        }

        //    Remove Image
        function removeImage(index) {
            const input = document.getElementById('images');
            selectedImageFiles.items.remove(index);
            input.files = selectedImageFiles.files;
            renderImagePreviews();
        }

        /* Render Image Preview  */
        function renderImagePreviews() {
            const grid = document.getElementById('image-preview-grid');
            const input = document.getElementById('images');
            grid.innerHTML = '';
            if (selectedImageFiles.files.length > 0) {
                grid.classList.remove('hidden');
                Array.from(selectedImageFiles.files).forEach(function(file, index) {
                    const div = document.createElement('div');
                    div.className =
                        'relative rounded-xl overflow-hidden border border-slate-200 aspect-video bg-slate-100 shadow-sm group';

                    const image = document.createElement('img');
                    image.className = 'w-full h-full object-cover';
                    image.alt = file.name;
                    const fileName = document.createElement('div');
                    fileName.className =
                        'absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs p-1 text-center';
                    fileName.textContent = file.name;

                    const removeButton = document.createElement('button');
                    removeButton.type = 'button';
                    removeButton.className =
                        'absolute top-2 right-2 z-10 w-7 h-7 rounded-full bg-black/60 hover:bg-rose-600 text-white flex items-center justify-center transition-colors';
                    removeButton.setAttribute('aria-label', `ลบรูปภาพ ${file.name}`);

                    removeButton.innerHTML = '<i class="fa-solid fa-xmark"></i>';
                    removeButton.addEventListener('click',
                        function(e) {
                            e.stopPropagation();
                            removeImage(index);
                        }
                    );

                    div.appendChild(image);
                    div.appendChild(fileName);
                    div.appendChild(removeButton);
                    grid.appendChild(div);

                    const reader = new FileReader();
                    reader.onload =
                        function(e) {
                            image.src = e.target.result;
                        };
                    reader.readAsDataURL(file);
                });
            } else {
                grid.classList.add('hidden');
            }

            input.files = selectedImageFiles.files;

        }
        /*DOM Ready*/
        document.addEventListener(
        'DOMContentLoaded',
        function() {
            const form = document.getElementById('ticketForm');
            const submitButton = document.querySelector('[data-submit-ticket]');
            const cancelButton = document.querySelector('[data-cancel-createTicket]');
            const modal = document.getElementById('ticketConfirmModal');
            const closeModalButton = document.getElementById('closeTicketModal');
            const backdrop = document.getElementById('ticketModalBackdrop');
            const editButton = document.getElementById('editTicketButton');
            const confirmButton = document.getElementById('confirmTicketButton');
            const imageDropArea = document.querySelector('.inputImageCreatePage');

            imageDropArea.addEventListener('dragover', function(event) {
                event.preventDefault();
            });

            imageDropArea.addEventListener('drop', function(event) {
                event.preventDefault();
                addImageFiles(event.dataTransfer.files);
            });

            // เปิด Modal
            function openConfirmModal() {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            }

            // เปิด Modal
            function closeConfirmModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
            }

            //สร้างข้อมูลสำหรับ Modal
            function prepareConfirmData() {
                const categorySelect = document.getElementById('category_id');
                const categoryText = categorySelect.options[categorySelect.selectedIndex]?.text?.trim() || '-';
                const asset = document.getElementById('asset_no').value.trim() || '-';
                const location = document.getElementById('location').value.trim() || '-';
                const symptom = document.getElementById('symptom').value.trim() || '-';

                /*แสดงข้อมูล */
                document.getElementById('confirmCategory').textContent = categoryText;
                document.getElementById('confirmAsset').textContent = asset;
                document.getElementById('confirmLocation').textContent = location;

                document.getElementById('confirmSymptom').textContent = symptom;

                // Images
                const imageContainer = document.getElementById('confirmImages');
                const imageCount = document.getElementById('confirmImageCount');
                const imageInput = document.getElementById('images');
                imageContainer.innerHTML = '';
                imageCount.textContent = '';
                if (imageInput && imageInput.files.length > 0) {
                    imageCount.textContent = `(${imageInput.files.length} รูป)`;
                    Array.from(imageInput.files).forEach(function(file) {
                        const wrapper = document.createElement('div');
                        wrapper.className =
                            'relative aspect-square rounded-xl overflow-hidden border border-slate-200 bg-slate-100';
                        const image = document.createElement('img');
                        image.className = 'w-full h-full object-cover';
                        image.alt = file.name;

                        const reader = new FileReader();
                        reader.onload = function(e) {
                            image.src = e.target.result;
                        };
                        reader.readAsDataURL(file);
                        wrapper.appendChild(image);
                        imageContainer.appendChild(wrapper);
                    });
                } else {
                    imageCount.textContent = '(ไม่ได้แนบรูปภาพ)';
                    imageContainer.innerHTML = `
                            <div class=" col-span-full rounded-xl border border-dashed border-slate-300 p-6 text-center text-slate-400 text-sm ">
                                <i class="fa-regular fa-image text-2xl mb-2"></i>
                                <div>
                                    ไม่ได้แนบรูปภาพ
                                </div>
                            </div>
                        `;
                }

        }

        if (form) {
            form.addEventListener(
                'submit',
                function(event) {
                    event.preventDefault();
                    if (!form.checkValidity()) {
                        form.reportValidity();
                        return;
                    }
                    prepareConfirmData();
                    openConfirmModal();
                }
            );
        }

        if (editButton) {
            editButton.addEventListener(
                'click',
                function() {
                    closeConfirmModal();
                }
            );
        }

        // ปุ่ม X
        if (closeModalButton) {
            closeModalButton.addEventListener(
                'click',
                function() {
                    closeConfirmModal();
                }
            );
        }

        if (backdrop) {
            backdrop.addEventListener(
                'click',
                function() {
                    closeConfirmModal();
                }
            );
        }

        // Confirm Submit
        if (confirmButton) {
            confirmButton.addEventListener('click',
                function() {
                    confirmButton.disabled = true;
                    confirmButton.innerHTML = `
                                <i class="
                                    fa-solid
                                    fa-spinner
                                    fa-spin
                                    mr-1
                                "></i>
                                กำลังส่งข้อมูล...
                            `;
                    form.submit();
                }
            );
        }

        if (cancelButton) {
            cancelButton.addEventListener(
                'click',
                function(event) {
                    event.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'ยืนยันการยกเลิก',
                        text: 'ข้อมูลที่กรอกไว้จะไม่ถูกบันทึก ต้องการออกจากหน้านี้หรือไม่?',
                        showCancelButton: true,
                        confirmButtonText: 'ยืนยันออกจากหน้า',
                        cancelButtonText: 'อยู่หน้านี้ต่อ',
                        confirmButtonColor: '#e11d48',
                        cancelButtonColor: '#64748b',
                        reverseButtons: true,
                        allowOutsideClick: false
                    }).then(
                        function(result) {
                            if (result.isConfirmed) {
                                window.location.href = cancelButton.href;
                            }
                        }
                    );
                }
            );
        }
        }
        );
    </script>

    @if (session('success'))
        <script>
            document.addEventListener(
                'DOMContentLoaded',
                function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'แจ้งซ่อมสำเร็จ',
                        text: @json(session('success')),
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#2563eb',
                        allowOutsideClick: false
                    });
                }
            );
        </script>
    @endif
</x-app-layout>
