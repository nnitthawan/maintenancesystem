<x-app-layout>
    <x-slot name="header">
        <div class="page-header">
            <div>
                <h1 class="page-title">เพิ่มผู้ใช้งาน</h1>
                <p class="page-description">สร้างบัญชีและกำหนดสิทธิ์การใช้งาน</p>
            </div><a href="{{ route('admin.users.index') }}" class="secondary-action"><i
                    class="fa-solid fa-arrow-left"></i>กลับรายการ</a>
        </div>
    </x-slot>
    <div class="data-page-stack">
        <div class="data-card p-6">
            <form method="POST" action="{{ route('admin.users.store') }}" class="grid grid-cols-1 gap-5 md:grid-cols-2">
                @csrf
                <div><label for="name"
                        class="mb-1 block text-sm font-medium text-slate-700">ชื่อ-นามสกุล</label><input id="name"
                        name="name" value="{{ old('name') }}" required class="modal-input">
                    @error('name')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                <div><label for="username"
                        class="mb-1 block text-sm font-medium text-slate-700">ชื่อผู้ใช้</label><input id="username"
                        name="username" value="{{ old('username') }}" required class="modal-input">
                    @error('username')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                <div><label for="phone"
                        class="mb-1 block text-sm font-medium text-slate-700">เบอร์โทรศัพท์</label><input id="phone"
                        name="phone" value="{{ old('phone') }}" class="modal-input"></div>
                <div><label for="department_id"
                        class="mb-1 block text-sm font-medium text-slate-700">แผนก</label><select id="department_id"
                        name="department_id" class="filter-select">
                        <option value="">ไม่ระบุ</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>
                                {{ $department->department_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label for="role_id" class="mb-1 block text-sm font-medium text-slate-700">บทบาท</label><select
                        id="role_id" name="role_id" required class="filter-select">
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>{{ $role->name }}
                            </option>
                        @endforeach
                    </select></div>
                <div><label for="password" class="mb-1 block text-sm font-medium text-slate-700">รหัสผ่าน</label><input
                        id="password" name="password" type="password" required class="modal-input"></div>
                <div><label for="password_confirmation"
                        class="mb-1 block text-sm font-medium text-slate-700">ยืนยันรหัสผ่าน</label><input
                        id="password_confirmation" name="password_confirmation" type="password" required
                        class="modal-input"></div>
                <div class="form-actions md:col-span-2"><a href="{{ route('admin.users.index') }}"
                        class="secondary-action">ยกเลิก</a><button type="submit" class="primary-action"><i
                            class="fa-solid fa-save"></i>บันทึกผู้ใช้งาน</button></div>
            </form>
        </div>
    </div>
</x-app-layout>
