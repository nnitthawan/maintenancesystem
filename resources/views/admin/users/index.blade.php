<x-app-layout>
    <x-slot name="header">
        <div class="page-header">
            <div>
                <h1 class="page-title">จัดการบัญชีผู้ใช้</h1>
                <p class="page-description">ค้นหา แก้ไข เพิ่ม และจัดการสิทธิ์ผู้ใช้งานในระบบ</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="page-action"><i
                    class="fa-solid fa-user-plus"></i>เพิ่มผู้ใช้งาน</a>
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
            <div class="data-card-header">
                <h2 class="section-title text-base font-bold text-slate-800"><i
                        class="fa-solid fa-users text-blue-600"></i>บัญชีผู้ใช้</h2>
                <div class="data-card-actions">
                    <button type="button" class="secondary-action" data-toggle-import>
                        <i class="fa-solid fa-upload"></i>นำเข้าข้อมูล
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
                                <td class="table-actions text-start"><a href="{{ route('admin.users.edit', $user) }}"
                                        class="table-link mr-3"><i class="fa-solid fa-pen-to-square mr-1"></i>แก้ไข</a>
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

        importToggle.addEventListener('click', function() {
            importModal.classList.add('is-open');
            document.getElementById('excel_file').focus();
        });

        function closeImportModal() {
            importModal.classList.remove('is-open');
            document.getElementById('excel_file').value = '';
        }

        document.querySelectorAll('[data-close-import]').forEach(function(button) {
            button.addEventListener('click', closeImportModal);
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
