<x-app-layout>
    <x-slot name="header">
        <div class="page-header">
            <div>
                <h2 class="page-title">
                    {{-- <i class="fa-solid fa-file-circle-plus text-emerald-600"></i> --}}
                    <span>แบบฟอร์มแจ้งซ่อม</span>
                </h2>
                <p class="page-description">กรอกรายละเอียดอุปกรณ์หรือสถานที่ที่ต้องการแจ้งซ่อมบำรุง</p>
            </div>
            <a href="{{ route('tickets.index') }}" class="secondary-action">
                <i class="fa-solid fa-arrow-left"></i>
                <span>ย้อนกลับ</span>
            </a>
        </div>
    </x-slot>

    <div class="data-page-stack">
        <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data" class="ticket-form">
            @csrf

            <!-- Reporter Info Card -->
            <div class="ticket-reporter-card">
                <div class="flex items-center gap-3">
                    <div
                        class="w-12 h-12 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-lg shadow-md">
                        {{ mb_substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800">{{ Auth::user()->name }}</h4>
                        <p class="text-xs text-slate-500">
                            เบอร์โทร: <span
                                class="font-medium text-slate-700">{{ Auth::user()->phone ?: 'ไม่ได้ระบุ' }}</span> |
                            สังกัด: <span
                                class="font-medium text-slate-700">{{ Auth::user()->department->department_name ?? 'ไม่ระบุ' }}</span>
                        </p>
                    </div>
                </div>
                <span class="px-3 py-1 bg-blue-600 text-white rounded-full text-xs font-semibold">
                    <i class="fa-solid fa-user-check me-1"></i> ผู้แจ้งซ่อม
                </span>
            </div>

            <div class="data-card ticket-form-card">
                @if ($errors->any())
                    <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-sm">
                        <div class="font-semibold flex items-center gap-2 mb-1">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <span>กรุณาตรวจสอบข้อมูลก่อนส่ง:</span>
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-xs">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Category Select -->
                    <div>
                        <label for="category_id" class="block text-sm font-semibold text-slate-700 mb-2">
                            หมวดหมู่การซ่อม <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <select id="category_id" name="category_id" required
                                class="w-full rounded-xl border border-slate-200 text-slate-800 text-sm focus:border-blue-500 focus:ring-blue-500 py-3 px-4 shadow-sm bg-slate-50/50">
                                <option value="" disabled selected>-- เลือกหมวดหมู่ปัญหา --</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->category_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('category_id')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Asset No -->
                    <div>
                        <label for="asset_no" class="block text-sm font-semibold text-slate-700 mb-2">
                            รหัสครุภัณฑ์ / เลขทะเบียนอุปกรณ์ <span class="text-slate-400 font-normal">(ถ้ามี)</span>
                        </label>
                        <div class="relative">
                            <input type="text" id="asset_no" name="asset_no" value="{{ old('asset_no') }}"
                                placeholder="เช่น EQ-67-0012 หรือ IT-PC-05"
                                class="w-full rounded-xl border border-slate-200 text-slate-800 text-sm focus:border-blue-500 focus:ring-blue-500 py-3 px-4 shadow-sm bg-slate-50/50">
                        </div>
                        @error('asset_no')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Location -->
                <div>
                    <label for="location" class="block text-sm font-semibold text-slate-700 mb-2">
                        สถานที่ / อาคาร / ชั้น / ห้อง <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="location" name="location" value="{{ old('location') }}" required
                        placeholder="เช่น อาคารอำนวยการ ชั้น 2 ห้องประชุมใหญ่ 1"
                        class="w-full rounded-xl border border-slate-200 text-slate-800 text-sm focus:border-blue-500 focus:ring-blue-500 py-3 px-4 shadow-sm bg-slate-50/50">
                    @error('location')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Symptom Description -->
                <div>
                    <label for="symptom" class="block text-sm font-semibold text-slate-700 mb-2">
                        รายละเอียดอาการเสีย / ปัญหาที่พบ <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="symptom" name="symptom" rows="4" required
                        placeholder="อธิบายอาการชำรุด หรือปัญหาที่เกิดขึ้นโดยละเอียด เช่น เครื่องเปิดไม่ติด มีเสียงดังผิดปกติ หรือไม่สามารถใช้งานได้..."
                        class="w-full rounded-xl border border-slate-200 text-slate-800 text-sm focus:border-blue-500 focus:ring-blue-500 p-4 shadow-sm bg-slate-50/50">{{ old('symptom') }}</textarea>
                    @error('symptom')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Image Upload Zone -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        แนบรูปภาพประกอบ <span class="text-slate-400 font-normal">(อัปโหลดได้หลายรูป, รองรับ JPG, PNG,
                            WEBP ไม่เกิน 5MB)</span>
                    </label>

                    <div class="border-2 border-dashed border-slate-200 hover:border-blue-400 bg-slate-50/60 rounded-2xl p-6 text-center transition-colors relative cursor-pointer group"
                        onclick="document.getElementById('images').click()">
                        <input type="file" id="images" name="images[]" multiple accept="image/*" class="hidden"
                            onchange="previewImages(event)">

                        <div
                            class="w-14 h-14 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mx-auto mb-3 text-xl group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                        </div>
                        <p class="text-sm font-medium text-slate-700">คลิกที่นี่ หรือ ลากรูปภาพมาวาง</p>
                        <p class="text-xs text-slate-400 mt-1">สามารถเลือกได้มากกว่า 1 รูปเพื่อประกอบการพิจารณาซ่อม</p>
                    </div>

                    <!-- Image Preview Container -->
                    <div id="image-preview-grid" class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-4 hidden"></div>
                </div>

                <!-- Form Action Buttons -->
                <div class="form-actions ticket-form-actions">
                    <a href="{{ route('tickets.index') }}"
                        class="px-6 py-3 rounded-xl border border-slate-200 text-slate-600 font-medium text-sm hover:bg-slate-100 transition-colors">
                        ยกเลิก
                    </a>
                    <button type="submit"
                        class="px-8 py-3 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-semibold text-sm shadow-lg hover:shadow-emerald-600/30 transition-all duration-200 flex items-center gap-2">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>ส่งใบแจ้งซ่อม</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- รูปภาพที่เลือก --}}
    <!-- JS script for image preview -->
    <script>
        const maxImageCount = 5;
        const selectedImageFiles = new DataTransfer();

        function previewImages(event) {
            const input = event.target;
            let hasExceededLimit = false;

            Array.from(input.files).forEach((file) => {
                if (file.type.startsWith('image/') && selectedImageFiles.files.length < maxImageCount) {
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

            input.files = selectedImageFiles.files;
            renderImagePreviews();
        }

        function removeImage(index) {
            const input = document.getElementById('images');

            selectedImageFiles.items.remove(index);
            input.files = selectedImageFiles.files;
            renderImagePreviews();
        }

        function renderImagePreviews() {
            const grid = document.getElementById('image-preview-grid');
            const input = document.getElementById('images');

            grid.innerHTML = '';

            if (selectedImageFiles.files.length > 0) {
                grid.classList.remove('hidden');
                Array.from(selectedImageFiles.files).forEach((file, index) => {
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
                    removeButton.addEventListener('click', () => removeImage(index));

                    div.appendChild(image);
                    div.appendChild(fileName);
                    div.appendChild(removeButton);
                    grid.appendChild(div);

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        image.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                });
            } else {
                grid.classList.add('hidden');
            }

            input.files = selectedImageFiles.files;
        }
    </script>
</x-app-layout>
