<x-app-layout>
    <x-slot name="header">
        <div class="page-header">
            <div>
            <h1 class="page-title">
                    {{-- <i class="fa-solid fa-layer-group text-blue-600"></i> --}}
                    ประเภทงานซ่อม
                </h1>
                <p class="page-description">จัดการประเภทงานซ่อมในระบบ</p>
            </div>
            <button type="button" onclick="openCreateModal()"
                class="page-action">
                <i class="fa-solid fa-plus"></i>
                เพิ่มประเภทงานซ่อม
            </button>
        </div>
    </x-slot>

    <div class="data-page-stack">
        <div class="data-card">
        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ประเภทงานซ่อม</th>
                        <th>จำนวนรายการแจ้งซ่อม</th>
                        <th class="table-actions">การจัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($categories as $category)
                        <tr class="transition-colors hover:bg-slate-50/80">
                            <td>{{ $categories->firstItem() + $loop->index }}</td>
                            <td><strong>{{ $category->category_name }}</strong></td>
                            <td>{{ $category->tickets_count }} รายการ</td>
                            <td class="table-actions">
                                <button type="button" onclick='openEditModal(@json($category))'
                                    class="table-link mr-3">
                                    <i class="fa-solid fa-pen-to-square mr-1"></i>แก้ไข
                                </button>
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="inline"
                                    data-delete-category="{{ $category->category_name }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" @disabled($category->tickets_count > 0)
                                        class="table-danger"
                                        title="{{ $category->tickets_count > 0 ? 'มีรายการแจ้งซ่อมอยู่' : '' }}">
                                        <i class="fa-solid fa-trash mr-1"></i>ลบ
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-state">ยังไม่มีประเภทงานซ่อม</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($categories->hasPages())
            <div class="pagination-wrap">{{ $categories->links() }}</div>
        @endif
        </div>
    </div>

    <div id="categoryModal" class="modal-backdrop"
        onclick="if (event.target === this) closeCategoryModal()">
        <div class="modal-panel">
            <div class="data-card-header">
                <h2 id="modalTitle" class="text-lg font-bold text-slate-800">เพิ่มประเภทงานซ่อม</h2>
                <button type="button" onclick="closeCategoryModal()" class="text-2xl text-slate-400 hover:text-slate-700">&times;</button>
            </div>
            <form id="categoryForm" method="POST" action="{{ route('admin.categories.store') }}">
                @csrf
                <div id="methodField"></div>
                <label for="category_name" class="mb-2 block text-sm font-medium text-slate-700">ชื่อประเภทงานซ่อม</label>
                <input id="category_name" name="category_name" required maxlength="255" class="modal-input">
                <div class="form-actions">
                    <button type="button" onclick="closeCategoryModal()" class="secondary-action">ยกเลิก</button>
                    <button type="submit" class="primary-action">บันทึก</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const categoryModal = document.getElementById('categoryModal');
        const categoryForm = document.getElementById('categoryForm');
        const categoryName = document.getElementById('category_name');
        const methodField = document.getElementById('methodField');

        function openCreateModal() {
            document.getElementById('modalTitle').textContent = 'เพิ่มประเภทงานซ่อม';
            categoryForm.action = '{{ route('admin.categories.store') }}';
            categoryForm.dataset.mode = 'create';
            methodField.innerHTML = '';
            categoryName.value = '';
            categoryModal.classList.add('is-open');
        }

        function openEditModal(category) {
            document.getElementById('modalTitle').textContent = 'แก้ไขประเภทงานซ่อม';
            categoryForm.action = '{{ url('admin/categories') }}/' + category.id;
            categoryForm.dataset.mode = 'edit';
            methodField.innerHTML = '<input type="hidden" name="_method" value="PUT">';
            categoryName.value = category.category_name;
            categoryModal.classList.add('is-open');
        }

        function closeCategoryModal() {
            categoryModal.classList.remove('is-open');
        }

        categoryForm.addEventListener('submit', function (event) {
            event.preventDefault();

            const isEdit = categoryForm.dataset.mode === 'edit';
            Swal.fire({
                icon: 'question',
                title: isEdit ? 'ยืนยันการแก้ไข' : 'ยืนยันการเพิ่มประเภทงานซ่อม',
                text: isEdit ? 'ต้องการบันทึกการแก้ไขประเภทงานซ่อมนี้หรือไม่?' : 'ต้องการเพิ่มประเภทงานซ่อมนี้หรือไม่?',
                showCancelButton: true,
                confirmButtonText: isEdit ? 'ยืนยันการแก้ไข' : 'ยืนยันการเพิ่ม',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#94a3b8'
            }).then(function (result) {
                if (result.isConfirmed) {
                    categoryForm.submit();
                }
            });
        });

        document.querySelectorAll('[data-delete-category]').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();

                Swal.fire({
                    icon: 'warning',
                    title: 'ยืนยันการลบ',
                    text: 'ต้องการลบประเภทงานซ่อม "' + form.dataset.deleteCategory + '" หรือไม่?',
                    showCancelButton: true,
                    confirmButtonText: 'ยืนยันการลบ',
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
    </script>
</x-app-layout>