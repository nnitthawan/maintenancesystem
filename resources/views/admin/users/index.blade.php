<x-app-layout>
    <x-slot name="header">
        <div class="page-header">
            <div>
                <h1 class="page-title">จัดการบัญชีผู้ใช้</h1>
                <p class="page-description">ค้นหา แก้ไข เพิ่ม และจัดการสิทธิ์ผู้ใช้งานในระบบ</p>
            </div>
            <button type="button" class="page-action" data-toggle-create>
                <i class="fa-solid fa-user-plus"></i>เพิ่มผู้ใช้งาน
            </button>
        </div>
    </x-slot>

    <div class="data-page-stack">
        <div class="filter-panel">
            <form method="GET" action="{{ route('admin.users.index') }}" class="filter-form user-filter-form">
                <div><label for="search" class="mb-1 block text-xs font-semibold text-slate-500">ค้นหา</label><input
                        id="search" name="search" value="{{ request('search') }}"
                        placeholder="ชื่อ ชื่อผู้ใช้ หรือเบอร์โทรศัพท์" class="filter-input"></div>
                <div><label for="role_id" class="mb-1 block text-xs font-semibold text-slate-500">บทบาท</label><select
                        id="role_id" name="role_id" class="filter-select">
                        <option value="">ทุกบทบาท</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected((string) request('role_id') === (string) $role->id)>{{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div><label for="department_id"
                        class="mb-1 block text-xs font-semibold text-slate-500">แผนก</label><select id="department_id"
                        name="department_id" class="filter-select">
                        <option value="">ทุกแผนก</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected((string) request('department_id') === (string) $department->id)>
                                {{ $department->department_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-actions self-end"><button type="submit" class="primary-action"><i
                            class="fa-solid fa-filter"></i>กรอง</button><a href="{{ route('admin.users.index') }}"
                        class="secondary-action">ล้าง</a></div>
            </form>
        </div>

        <div class="data-card">
            <div class="data-cardListUsers-header">
                <h2 class="section-title text-base font-bold text-slate-800">
                    {{-- <i class="fa-solid fa-users text-blue-600"></i> --}}
                    บัญชีผู้ใช้</h2>
                <div class="data-card-actions">
                    <button type="button" class="secondary-action" data-toggle-import>
                        <i class="fa-solid fa-download"></i></i>นำเข้าข้อมูล
                    </button>
                    <a href="{{ route('admin.users.template') }}" class="secondary-action">
                        <i class="fa-solid fa-download"></i>Template
                    </a>
                </div>
            </div>
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ผู้ใช้งาน</th>
                            <th>ชื่อผู้ใช้</th>
                            <th>แผนก</th>
                            <th>บทบาท</th>
                            <th>การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td><strong>{{ $user->name }}</strong>
                                    <div class="text-xs text-slate-400">{{ $user->phone ?: 'ไม่มีเบอร์โทรศัพท์' }}
                                    </div>
                                </td>
                                <td>{{ $user->username }}</td>
                                <td>{{ $user->department->department_name ?? 'ไม่ระบุ' }}</td>
                                <td><span
                                        class="status-pill status-{{ $user->isAdmin() ? 'progress' : 'completed' }}"><span
                                            class="status-dot"></span>{{ $user->roleRelation->name ?? ($user->isAdmin() ? 'admin' : 'user') }}</span>
                                </td>
                                <td class="table-actions text-start"><button type="button"
                                        class="table-link mr-3" data-open-edit-user="{{ $user->id }}">
                                        <i class="fa-solid fa-pen-to-square mr-1"></i>แก้ไข</button>
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                        class="inline" data-delete-user="{{ $user->name }}">@csrf
                                        @method('DELETE')<button type="submit" class="table-danger"
                                            @disabled($user->id === Auth::id())
                                            title="{{ $user->id === Auth::id() ? 'ไม่สามารถลบบัญชีตัวเอง' : '' }}"><i
                                                class="fa-solid fa-trash mr-1"></i>ลบ</button></form>
                                </td>
                        </tr>@empty<tr>
                                <td colspan="5" class="empty-state">ยังไม่มีบัญชีผู้ใช้</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())
                <div class="pagination-wrap">{{ $users->links() }}</div>
            @endif
        </div>
    </div>

    @foreach ($users as $user)
        <div class="modal-backdrop {{ (string) old('_edit_user_id') === (string) $user->id ? 'is-open' : '' }}"
            data-edit-user-modal="{{ $user->id }}"
            onclick="if (event.target === this) closeEditUserModal(this)" role="presentation">
            @include('admin.users.edit-modal', [
                'user' => $user,
                'restoreOldInput' => (string) old('_edit_user_id') === (string) $user->id,
            ])
        </div>
    @endforeach

    <div class="modal-backdrop {{ old('_create_user_form') || request()->boolean('create') ? 'is-open' : '' }}"
        data-create-modal onclick="if (event.target === this) closeCreateModal()" role="presentation">
        @include('admin.users.create')
    </div>

    <div id="importModal" class="modal-backdrop" data-import-modal
        onclick="if (event.target === this) closeImportModal()">
        <div class="modal-panel">
            <div class="data-card-header">
                <h2 class="text-lg font-bold text-slate-800">นำเข้าข้อมูลผู้ใช้งาน</h2>
                <button type="button" class="text-2xl text-slate-400 hover:text-slate-700"
                    data-close-import aria-label="ปิดหน้าต่าง">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.users.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="space-y-3 p-6">
                    <p class="text-sm text-slate-500">เลือกไฟล์ Excel หรือ CSV ตามรูปแบบ Template ที่ดาวน์โหลด</p>
                    <div>
                        <label for="excel_file" class="mb-2 block text-sm font-medium text-slate-700">ไฟล์ข้อมูล</label>
                        <input id="excel_file" name="excel_file" type="file" accept=".xlsx,.xls,.csv" required
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                    </div>
                </div>
                <div class="form-actions px-6 pb-6">
                    <button type="button" class="secondary-action" data-close-import>ยกเลิก</button>
                    <button type="submit" class="primary-action"><i class="fa-solid fa-cloud-arrow-up"></i>อัปโหลดข้อมูล</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const importToggle = document.querySelector('[data-toggle-import]');
        const importModal = document.querySelector('[data-import-modal]');
        const createToggle = document.querySelector('[data-toggle-create]');
        const createModal = document.querySelector('[data-create-modal]');
        const editUserModals = document.querySelectorAll('[data-edit-user-modal]');

        importToggle.addEventListener('click', function() {
            importModal.classList.add('is-open');
            updateModalScrollLock();
            document.getElementById('excel_file').focus();
        });

        function closeImportModal() {
            importModal.classList.remove('is-open');
            document.getElementById('excel_file').value = '';
            updateModalScrollLock();
        }

        createToggle.addEventListener('click', openCreateModal);

        function openCreateModal() {
            createModal.classList.add('is-open');
            updateModalScrollLock();
            document.getElementById('create-name').focus();
        }

        function closeCreateModal() {
            createModal.classList.remove('is-open');
            updateModalScrollLock();
            createToggle.focus();
        }

        function updateModalScrollLock() {
            document.body.classList.toggle(
                'overflow-hidden',
                createModal.classList.contains('is-open') ||
                    importModal.classList.contains('is-open') ||
                    Array.from(editUserModals).some(function(modal) {
                        return modal.classList.contains('is-open');
                    })
            );
        }

        document.querySelectorAll('[data-open-edit-user]').forEach(function(button) {
            button.addEventListener('click', function() {
                const modal = document.querySelector(
                    '[data-edit-user-modal="' + button.dataset.openEditUser + '"]'
                );
                modal.classList.add('is-open');
                updateModalScrollLock();
                modal.querySelector('input[name="name"]').focus();
            });
        });

        function closeEditUserModal(modal) {
            modal.classList.remove('is-open');
            updateModalScrollLock();
            document.querySelector('[data-open-edit-user="' + modal.dataset.editUserModal + '"]').focus();
        }

        document.querySelectorAll('[data-close-edit-user]').forEach(function(button) {
            button.addEventListener('click', function() {
                closeEditUserModal(button.closest('[data-edit-user-modal]'));
            });
        });

        document.querySelectorAll('[data-close-import]').forEach(function(button) {
            button.addEventListener('click', closeImportModal);
        });

        document.querySelectorAll('[data-close-create]').forEach(function(button) {
            button.addEventListener('click', closeCreateModal);
        });

        document.querySelectorAll('[data-password-toggle]').forEach(function(button) {
            button.addEventListener('click', function() {
                const password = document.getElementById(button.dataset.passwordToggle);
                const isVisible = password.type === 'text';

                password.type = isVisible ? 'password' : 'text';
                button.setAttribute('aria-pressed', String(!isVisible));
                button.setAttribute('aria-label', isVisible ? 'แสดงรหัสผ่าน' : 'ซ่อนรหัสผ่าน');
                button.querySelector('i').classList.toggle('fa-eye', !isVisible);
                button.querySelector('i').classList.toggle('fa-eye-slash', isVisible);
            });
        });

        document.querySelectorAll('[data-edit-user-form]').forEach(function(form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault();

                const confirmMessage = 'ต้องการบันทึกข้อมูลบัญชีนี้หรือไม่?';
                if (typeof window.Swal === 'undefined') {
                    if (window.confirm(confirmMessage)) form.submit();
                    return;
                }

                Swal.fire({
                    icon: 'question',
                    title: 'ยืนยันการแก้ไข',
                    text: confirmMessage,
                    showCancelButton: true,
                    confirmButtonText: 'ยืนยัน',
                    cancelButtonText: 'ยกเลิก',
                    confirmButtonColor: '#2563eb',
                    cancelButtonColor: '#94a3b8'
                }).then(function(result) {
                    if (result.isConfirmed) form.submit();
                });
            });
        });

        const createUserForm = document.querySelector('[data-create-user-form]');
        const createPassword = document.getElementById('create-password');
        const createPasswordConfirmation = document.getElementById('create-password-confirmation');
        const passwordMismatchMessage = document.querySelector('[data-password-mismatch]');

        function validateCreatePassword() {
            const mismatch = createPasswordConfirmation.value !== '' &&
                createPassword.value !== createPasswordConfirmation.value;

            createPasswordConfirmation.setCustomValidity(mismatch ? 'รหัสผ่านยืนยันไม่ตรงกัน' : '');
            passwordMismatchMessage.classList.toggle('hidden', !mismatch);
        }

        createPassword.addEventListener('input', validateCreatePassword);
        createPasswordConfirmation.addEventListener('input', validateCreatePassword);

        createUserForm.addEventListener('submit', function(event) {
            validateCreatePassword();
            if (!createUserForm.checkValidity()) {
                event.preventDefault();
                createUserForm.reportValidity();
            }
        });

        if (createModal.classList.contains('is-open')) {
            updateModalScrollLock();
            document.getElementById('create-name').focus();
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                if (createModal.classList.contains('is-open')) closeCreateModal();
                if (importModal.classList.contains('is-open')) closeImportModal();
                editUserModals.forEach(function(modal) {
                    if (modal.classList.contains('is-open')) closeEditUserModal(modal);
                });
            }
        });

        document.querySelectorAll('[data-delete-user]').forEach(function(form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'ยืนยันการลบบัญชี',
                    text: 'ต้องการลบบัญชี "' + form.dataset.deleteUser + '" หรือไม่?',
                    showCancelButton: true,
                    confirmButtonText: 'ยืนยันการลบ',
                    cancelButtonText: 'ยกเลิก',
                    confirmButtonColor: '#e11d48',
                    cancelButtonColor: '#94a3b8'
                }).then(function(result) {
                    if (result.isConfirmed) form.submit();
                });
            });
        });
    </script>
</x-app-layout>
