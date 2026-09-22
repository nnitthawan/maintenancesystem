<x-app-layout>
    <x-slot name="header">
        <div class="page-header">
            <div>
            <h1 class="page-title">
                    {{-- <i class="fa-solid fa-building text-blue-600"></i> --}}
                    กลุ่มงาน / แผนก
                </h1>
                <p class="page-description">จัดการข้อมูลกลุ่มงานและแผนกในระบบ</p>
            </div>
            <button type="button" onclick="openCreateModal()"
                class="page-action">
                <i class="fa-solid fa-plus"></i>
                เพิ่มกลุ่มงาน
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
                            <th>ชื่อกลุ่มงาน / แผนก</th>
                            <th>จำนวนผู้ใช้งาน</th>
                            <th class="table-actions">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    @forelse ($departments as $department)
                        <tr class="transition-colors hover:bg-slate-50/80">
                            <td>{{ $departments->firstItem() + $loop->index }}</td>
                            <td><strong>{{ $department->department_name }}</strong></td>
                            <td>
                                <span class="status-pill status-progress">
                                    <i class="fa-solid fa-users"></i>
                                    {{ $department->users_count }} คน
                                </span>
                            </td>
                            <td class="table-actions">
                                <button type="button" onclick='openEditModal(@json($department))'
                                    class="table-link mr-3">
                                    <i class="fa-solid fa-pen-to-square mr-1"></i>แก้ไข
                                </button>
                                <form method="POST" action="{{ route('admin.departments.destroy', $department) }}" class="inline"
                                    data-delete-department="{{ $department->department_name }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" @disabled($department->users_count > 0)
                                        class="table-danger"
                                        title="{{ $department->users_count > 0 ? 'ยังมีผู้ใช้งานสังกัดอยู่' : '' }}">
                                        <i class="fa-solid fa-trash mr-1"></i>ลบ
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-state">ยังไม่มีกลุ่มงานหรือแผนก</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if ($departments->hasPages())
                <div class="pagination-wrap">{{ $departments->links() }}</div>
            @endif
        </div>
    </div>

    <div id="departmentModal" class="modal-backdrop"
        onclick="if (event.target === this) closeDepartmentModal()">
        <div class="modal-panel">
            <div class="data-card-header">
                <h2 id="modalTitle" class="text-lg font-bold text-slate-800">เพิ่มกลุ่มงาน / แผนก</h2>
                <button type="button" onclick="closeDepartmentModal()" class="text-2xl text-slate-400 hover:text-slate-700">&times;</button>
            </div>
            <form id="departmentForm" method="POST" action="{{ route('admin.departments.store') }}">
                @csrf
                <div id="methodField"></div>
                <label for="department_name" class="mb-2 block text-sm font-medium text-slate-700">ชื่อกลุ่มงาน / แผนก</label>
                <input id="department_name" name="department_name" required maxlength="150"
                    placeholder="เช่น กลุ่มงานเทคโนโลยีสารสนเทศ" class="modal-input">
                <div class="form-actions">
                    <button type="button" onclick="closeDepartmentModal()" class="secondary-action">ยกเลิก</button>
                    <button type="submit" class="primary-action">บันทึก</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const departmentModal = document.getElementById('departmentModal');
        const departmentForm = document.getElementById('departmentForm');
        const departmentName = document.getElementById('department_name');
        const methodField = document.getElementById('methodField');

        function openCreateModal() {
            document.getElementById('modalTitle').textContent = 'เพิ่มกลุ่มงาน / แผนก';
            departmentForm.action = '{{ route('admin.departments.store') }}';
            departmentForm.dataset.mode = 'create';
            methodField.innerHTML = '';
            departmentName.value = '';
            departmentModal.classList.add('is-open');
        }

        function openEditModal(department) {
            document.getElementById('modalTitle').textContent = 'แก้ไขกลุ่มงาน / แผนก';
            departmentForm.action = '{{ url('admin/departments') }}/' + department.id;
            departmentForm.dataset.mode = 'edit';
            methodField.innerHTML = '<input type="hidden" name="_method" value="PUT">';
            departmentName.value = department.department_name;
            departmentModal.classList.add('is-open');
        }

        function closeDepartmentModal() {
            departmentModal.classList.remove('is-open');
        }

        departmentForm.addEventListener('submit', function (event) {
            event.preventDefault();

            const isEdit = departmentForm.dataset.mode === 'edit';
            Swal.fire({
                icon: 'question',
                title: isEdit ? 'ยืนยันการแก้ไข' : 'ยืนยันการเพิ่มกลุ่มงาน',
                text: isEdit ? 'ต้องการบันทึกการแก้ไขกลุ่มงานนี้หรือไม่?' : 'ต้องการเพิ่มกลุ่มงานนี้หรือไม่?',
                showCancelButton: true,
                confirmButtonText: isEdit ? 'ยืนยันการแก้ไข' : 'ยืนยันการเพิ่ม',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#94a3b8'
            }).then(function (result) {
                if (result.isConfirmed) {
                    departmentForm.submit();
                }
            });
        });

        document.querySelectorAll('[data-delete-department]').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();

                Swal.fire({
                    icon: 'warning',
                    title: 'ยืนยันการลบ',
                    text: 'ต้องการลบกลุ่มงาน "' + form.dataset.deleteDepartment + '" หรือไม่?',
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