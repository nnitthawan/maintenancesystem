<div class="modal-panel user-create-modal-panel" role="dialog" aria-modal="true"
    aria-labelledby="createUserModalTitle">
    <div class="data-card-header">
        <div>
            <h2 id="createUserModalTitle" class="text-lg font-bold text-slate-800">เพิ่มผู้ใช้งาน</h2>
            <p class="text-sm text-slate-500">สร้างบัญชีและกำหนดสิทธิ์การใช้งาน</p>
        </div>
        <button type="button" class="text-2xl text-slate-400 hover:text-slate-700"
            data-close-create aria-label="ปิดหน้าต่าง">&times;</button>
    </div>

    <form method="POST" action="{{ route('admin.users.store') }}" class="formDetailAddUser"
        data-create-user-form>
        @csrf
        <input type="hidden" name="_create_user_form" value="1">

        <div>
            <label for="create-name" class="mb-1 block text-sm font-medium text-slate-700">ชื่อ-นามสกุล</label>
            <input id="create-name" placeholder="ชื่อ-นามสกุล" name="name" value="{{ old('name') }}" required maxlength="255"
                autocomplete="name" class="modal-input" @if ($errors->has('name')) aria-invalid="true" @endif>
            @error('name')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="create-username" class="mb-1 block text-sm font-medium text-slate-700">ชื่อผู้ใช้</label>
            <input id="create-username" placeholder="ชื่อผู้ใช้" name="username" value="{{ old('username') }}" required maxlength="100"
                autocomplete="username" class="modal-input"
                @if ($errors->has('username')) aria-invalid="true" @endif>
            @error('username')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="create-phone" class="mb-1 block text-sm font-medium text-slate-700">เบอร์โทรศัพท์</label>
            <input id="create-phone" placeholder="เบอร์โทรศัพท์" name="phone" type="tel" value="{{ old('phone') }}" maxlength="10"
                autocomplete="tel" class="modal-input" @if ($errors->has('phone')) aria-invalid="true" @endif>
            @error('phone')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="create-department-id" class="mb-1 block text-sm font-medium text-slate-700">แผนก</label>
            <select id="create-department-id" name="department_id" class="filter-select"
                @if ($errors->has('department_id')) aria-invalid="true" @endif>
                <option value="">ไม่ระบุ</option>
                @foreach ($departments as $department)
                    <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>
                        {{ $department->department_name }}
                    </option>
                @endforeach
            </select>
            @error('department_id')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="create-role-id" class="mb-1 block text-sm font-medium text-slate-700">บทบาท</label>
            <select id="create-role-id" name="role_id" required class="filter-select"
                @if ($errors->has('role_id')) aria-invalid="true" @endif>
                <option value="">-- เลือกบทบาท --</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
            @error('role_id')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="create-password" class="mb-1 block text-sm font-medium text-slate-700">รหัสผ่าน</label>
            <div class="user-create-password-field">
                <input id="create-password" name="password" type="password" required minlength="6"
                    autocomplete="new-password" class="modal-input"
                    @if ($errors->has('password')) aria-invalid="true" @endif>
                <button type="button" class="user-password-toggle" data-password-toggle="create-password"
                    aria-label="แสดงรหัสผ่าน" aria-pressed="false">
                    <i class="fa-solid fa-eye-slash" aria-hidden="true"></i>
                </button>
            </div>
            <p class="mt-1 text-xs text-slate-400">รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร</p>
            @error('password')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="create-password-confirmation"
                class="mb-1 block text-sm font-medium text-slate-700">ยืนยันรหัสผ่าน</label>
            <div class="user-create-password-field">
                <input id="create-password-confirmation" name="password_confirmation" type="password" required
                    minlength="6" autocomplete="new-password" class="modal-input">
                <button type="button" class="user-password-toggle"
                    data-password-toggle="create-password-confirmation" aria-label="แสดงรหัสผ่าน" aria-pressed="false">
                    <i class="fa-solid fa-eye-slash" aria-hidden="true"></i>
                </button>
            </div>
            <p class="mt-1 hidden text-xs text-rose-600" data-password-mismatch>รหัสผ่านยืนยันไม่ตรงกัน</p>
        </div>

        <div class="form-actions md:col-span-2">
            <button type="button" class="secondary-action user-create-cancel-action"
                data-close-create>ยกเลิก</button>
            <button type="submit" class="primary-action">
                บันทึกผู้ใช้งาน
            </button>
        </div>
    </form>
</div>
