<section class="modal-panel user-create-modal-panel" role="dialog" aria-modal="true"
    aria-labelledby="editUserModalTitle-{{ $user->id }}">
    <div class="data-card-header">
        <div>
            <h2 id="editUserModalTitle-{{ $user->id }}" class="text-lg font-bold text-slate-800">แก้ไขบัญชีผู้ใช้</h2>
            <p class="text-sm text-slate-500">ปรับปรุงข้อมูลและสิทธิ์ของ {{ $user->name }}</p>
        </div>
        <button type="button" class="text-2xl text-slate-400 hover:text-slate-700"
            data-close-edit-user aria-label="ปิดหน้าต่าง">&times;</button>
    </div>

    <form method="POST" action="{{ route('admin.users.update', $user) }}"
        class="formDetaiEditUser" data-edit-user-form>
        @csrf
        @method('PUT')
        <input type="hidden" name="_edit_user_id" value="{{ $user->id }}">

        <div>
            <label for="edit-name-{{ $user->id }}" class="mb-1 block text-sm font-medium text-slate-700">ชื่อ-นามสกุล</label>
            <input id="edit-name-{{ $user->id }}" name="name"
                value="{{ $restoreOldInput ? old('name', $user->name) : $user->name }}" required maxlength="255"
                autocomplete="name" class="modal-input"
                @if ($restoreOldInput && $errors->has('name')) aria-invalid="true" @endif>
            @if ($restoreOldInput)
                @error('name')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            @endif
        </div>

        <div>
            <label for="edit-username-{{ $user->id }}" class="mb-1 block text-sm font-medium text-slate-700">ชื่อผู้ใช้</label>
            <input id="edit-username-{{ $user->id }}" name="username"
                value="{{ $restoreOldInput ? old('username', $user->username) : $user->username }}" required
                maxlength="100" autocomplete="username" class="modal-input"
                @if ($restoreOldInput && $errors->has('username')) aria-invalid="true" @endif>
            @if ($restoreOldInput)
                @error('username')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            @endif
        </div>

        <div>
            <label for="edit-phone-{{ $user->id }}" class="mb-1 block text-sm font-medium text-slate-700">เบอร์โทรศัพท์</label>
            <input id="edit-phone-{{ $user->id }}" name="phone" type="tel"
                value="{{ $restoreOldInput ? old('phone', $user->phone) : $user->phone }}" maxlength="20"
                autocomplete="tel" class="modal-input"
                @if ($restoreOldInput && $errors->has('phone')) aria-invalid="true" @endif>
            @if ($restoreOldInput)
                @error('phone')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            @endif
        </div>

        <div>
            <label for="edit-department-{{ $user->id }}"
                class="mb-1 block text-sm font-medium text-slate-700">แผนก</label>
            <select id="edit-department-{{ $user->id }}" name="department_id" class="filter-select"
                @if ($restoreOldInput && $errors->has('department_id')) aria-invalid="true" @endif>
                <option value="">ไม่ระบุ</option>
                @foreach ($departments as $department)
                    <option value="{{ $department->id }}" @selected(($restoreOldInput ? old('department_id', $user->department_id) : $user->department_id) == $department->id)>
                        {{ $department->department_name }}
                    </option>
                @endforeach
            </select>
            @if ($restoreOldInput)
                @error('department_id')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            @endif
        </div>

        <div>
            <label for="edit-role-{{ $user->id }}" class="mb-1 block text-sm font-medium text-slate-700">บทบาท</label>
            <select id="edit-role-{{ $user->id }}" name="role_id" required class="filter-select"
                @if ($restoreOldInput && $errors->has('role_id')) aria-invalid="true" @endif>
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}" @selected(($restoreOldInput ? old('role_id', $user->role_id) : $user->role_id) == $role->id)>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
            @if ($restoreOldInput)
                @error('role_id')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            @endif
        </div>

        <div>
            <label for="edit-password-{{ $user->id }}" class="mb-1 block text-sm font-medium text-slate-700">
                รหัสผ่านใหม่ <span class="text-xs font-normal text-slate-400">(เว้นว่างหากไม่ต้องการเปลี่ยน)</span>
            </label>
            <div class="user-create-password-field">
                <input id="edit-password-{{ $user->id }}" name="password" type="password" minlength="6"
                    autocomplete="new-password" class="modal-input"
                    @if ($restoreOldInput && $errors->has('password')) aria-invalid="true" @endif>
                <button type="button" class="user-password-toggle"
                    data-password-toggle="edit-password-{{ $user->id }}" aria-label="แสดงรหัสผ่าน"
                    aria-pressed="false">
                    <i class="fa-solid fa-eye-slash" aria-hidden="true"></i>
                </button>
            </div>
            @if ($restoreOldInput)
                @error('password')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            @endif
        </div>

        <div>
            <label for="edit-password-confirmation-{{ $user->id }}"
                class="mb-1 block text-sm font-medium text-slate-700">ยืนยันรหัสผ่านใหม่</label>
            <div class="user-create-password-field">
                <input id="edit-password-confirmation-{{ $user->id }}" name="password_confirmation"
                    type="password" minlength="6" autocomplete="new-password" class="modal-input">
                <button type="button" class="user-password-toggle"
                    data-password-toggle="edit-password-confirmation-{{ $user->id }}"
                    aria-label="แสดงรหัสผ่าน" aria-pressed="false">
                    <i class="fa-solid fa-eye-slash" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <div class="form-actions md:col-span-2">
            <button type="button" class="secondary-action user-create-cancel-action"
                data-close-edit-user>ยกเลิก</button>
            <button type="submit" class="primary-action">บันทึกการแก้ไข</button>
        </div>
    </form>
</section>
