<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            {{-- <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                <i class="fa-solid fa-user-gear text-lg"></i>
            </div> --}}
            <div>
                <h2 class="text-2xl font-bold leading-tight text-slate-800">ข้อมูลส่วนตัว</h2>
                <p class="mt-1 text-sm text-slate-500">จัดการข้อมูลบัญชีและความปลอดภัยของคุณ</p>
            </div>
        </div>
    </x-slot>

    @php($showPasswordTab = $errors->updatePassword->isNotEmpty())
    @php($showTwoFactorTab = $errors->has('two_factor') || in_array(session('status'), ['two-factor-setup', 'two-factor-required'], true))

    <div class="profile-page profile-layout {{ $user->isAdmin() ? 'profile-layout-admin' : 'mx-auto' }}" data-profile-tabs>
            <aside class="profile-sidebar">
                <div class="profile-user-summary">
                    <div class="profile-user-avatar" aria-hidden="true">
                        {{ mb_substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <p class="profile-user-name">{{ Auth::user()->name }}</p>
                    <p class="profile-user-department">
                        {{ Auth::user()->department->department_name ?? 'ไม่ระบุหน่วยงาน' }}
                    </p>
                </div>
                <p class="profile-sidebar-title">ตั้งค่าบัญชี</p>
                <button type="button" class="profile-tab {{ !$showPasswordTab && !$showTwoFactorTab ? 'is-active' : '' }}" data-tab-target="profile-info">
                    <i class="fa-solid fa-id-card"></i><span>ข้อมูลส่วนตัว</span>
                </button>
                <button type="button" class="profile-tab {{ $showTwoFactorTab ? 'is-active' : '' }}" data-tab-target="profile-two-factor">
                    <i class="fa-solid fa-shield-halved"></i><span>ยืนยันตัวตน 2 ขั้นตอน</span>
                </button>
                <button type="button" class="profile-tab {{ $showPasswordTab && !$showTwoFactorTab ? 'is-active' : '' }}" data-tab-target="profile-password">
                    <i class="fa-solid fa-lock"></i><span>เปลี่ยนรหัสผ่าน</span>
                </button>
                <form method="POST" action="{{ route('logout') }}" class="profile-logout-form">
                    @csrf
                    <button type="submit" class="profile-tab profile-logout-button">
                        <i class="fa-solid fa-right-from-bracket"></i><span>ออกจากระบบ</span>
                    </button>
                </form>
            </aside>

            <section id="profile-info" class="profile-card profile-tab-panel p-6 {{ $showPasswordTab || $showTwoFactorTab ? 'is-hidden' : '' }}">
            <div class="profile-card-header">
                <h3 class="flex items-center gap-2 text-lg font-bold text-slate-800">
                    <i class="fa-solid fa-id-card text-blue-600"></i>
                    ข้อมูลบัญชี
                </h3>
                <p class="mt-1 text-sm text-slate-500">แก้ไขชื่อ ชื่อผู้ใช้ และเบอร์โทรศัพท์</p>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" class="profile-form mt-6 space-y-5" data-confirm-form="ยืนยันการแก้ไขข้อมูลส่วนตัวหรือไม่?">
                @csrf
                @method('PATCH')

                <div class="form-group">
                    <label for="name">ชื่อ-นามสกุล</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required
                        autofocus>
                </div>

                <div class="form-group">
                    <label for="username">ชื่อผู้ใช้</label>
                    <input id="username" type="text" name="username" value="{{ old('username', $user->username) }}"
                        required>
                </div>

                <div class="form-group">
                    <label for="phone">เบอร์โทรศัพท์</label>
                    <input id="phone" type="tel" name="phone" value="{{ old('phone', $user->phone) }}"
                        maxlength="10" {{ $user->isAdmin() ? '' : 'required' }}>
                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2.5 text-white hover:bg-blue-700">บันทึกข้อมูล</button>
                    <button type="reset" class="profile-cancel-button" data-cancel-form>ยกเลิก</button>
                </div>
            </form>
        </section>

            <section id="profile-two-factor" class="profile-card profile-tab-panel p-6 {{ !$showTwoFactorTab ? 'is-hidden' : '' }}">
                <div class="profile-card-header">
                    <h3 class="flex items-center gap-2 text-lg font-bold text-slate-800">
                        <i class="fa-solid fa-shield-halved text-emerald-600"></i>
                        Google Authenticator
                    </h3>
                        <p class="mt-1 text-sm text-slate-500">เพิ่มความปลอดภัยให้บัญชีด้วยรหัสยืนยัน 6 หลัก</p>
                </div>

                @if ($user->two_factor_enabled)
                    <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                        เปิดใช้งาน 2FA แล้ว บัญชีนี้จะต้องกรอกรหัสจาก Google Authenticator ทุกครั้งที่เข้าสู่ระบบ
                    </div>
                    <form method="POST" action="{{ route('two-factor.disable') }}" class="profile-form mt-6 space-y-5" data-confirm-form="การปิด 2FA จะลดความปลอดภัยของบัญชีแอดมิน">
                        @csrf
                        @method('DELETE')
                        <div class="form-group">
                            <label for="two_factor_disable_password">รหัสผ่านปัจจุบันเพื่อยืนยัน</label>
                            <input id="two_factor_disable_password" type="password" name="password" required autocomplete="current-password">
                        </div>
                        <button type="submit" class="rounded-xl bg-red-600 px-4 py-2.5 text-white hover:bg-red-700">ปิดใช้งาน 2FA</button>
                    </form>
                @elseif ($twoFactorQrCode)
                    <div class="mt-6 space-y-4 text-sm text-slate-600">
                        <p>1. เปิด Google Authenticator แล้วสแกน QR Code ด้านล่าง</p>
                        <div class="w-fit rounded-xl border bg-white p-3">
                            <img src="{{ $twoFactorQrCode }}" alt="QR Code สำหรับ Google Authenticator" width="200" height="200">
                        </div>
                        <p>2. กรอกรหัส 6 หลักจากแอปเพื่อยืนยันการตั้งค่า</p>
                        <form method="POST" action="{{ route('two-factor.confirm') }}" class="profile-form space-y-4">
                            @csrf
                            <div class="form-group">
                                <label for="two_factor_code">รหัสยืนยัน 6 หลัก</label>
                                <input id="two_factor_code" type="text" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autocomplete="one-time-code">
                                <x-input-error :messages="$errors->get('two_factor')" class="mt-2" />
                            </div>
                            <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-white hover:bg-emerald-700">ยืนยันและเปิดใช้งาน</button>
                        </form>
                    </div>
                @else
                    <form method="POST" action="{{ route('two-factor.enable') }}" class="mt-6">
                        @csrf
                        <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-white hover:bg-emerald-700">เริ่มตั้งค่า Google Authenticator</button>
                    </form>
                @endif
            </section>

        <section id="profile-password" class="profile-card profile-tab-panel p-6 {{ !$showPasswordTab || $showTwoFactorTab ? 'is-hidden' : '' }}">
            <div class="profile-card-header">
                <h3 class="flex items-center gap-2 text-lg font-bold text-slate-800">
                    <i class="fa-solid fa-lock text-amber-600"></i>
                    เปลี่ยนรหัสผ่าน
                </h3>
                <p class="mt-1 text-sm text-slate-500">กรอกรหัสผ่านเดิมก่อนตั้งรหัสผ่านใหม่</p>
            </div>

            <form method="POST" action="{{ route('password.update') }}"
                class="profile-form profile-password-form mt-6 space-y-5" data-confirm-form="ยืนยันการเปลี่ยนรหัสผ่านหรือไม่?">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="current_password">รหัสผ่านเดิม</label>
                    <div class="profile-password-input">
                        <input id="current_password" name="current_password" type="password"
                            autocomplete="current-password" required />
                        <button type="button" class="profile-password-toggle" data-password-target="current_password" aria-label="แสดงรหัสผ่าน">
                            <i class="fa-solid fa-eye-slash"></i>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
                </div>

                <div class="form-group">
                    <label for="password">รหัสผ่านใหม่</label>
                    <div class="profile-password-input">
                        <input id="password" name="password" type="password"
                            autocomplete="new-password" required />
                        <button type="button" class="profile-password-toggle" data-password-target="password" aria-label="แสดงรหัสผ่าน">
                            <i class="fa-solid fa-eye-slash"></i>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
                </div>

                <div class="form-group">
                    <label for="password_confirmation">ยืนยันรหัสผ่านใหม่</label>
                    <div class="profile-password-input">
                        <input id="password_confirmation" name="password_confirmation" type="password"
                            autocomplete="new-password" required />
                        <button type="button" class="profile-password-toggle" data-password-target="password_confirmation" aria-label="แสดงรหัสผ่าน">
                            <i class="fa-solid fa-eye-slash"></i>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="rounded-xl bg-amber-600 px-4 py-2.5 text-white hover:bg-amber-700">เปลี่ยนรหัสผ่าน</button>
                    <button type="reset" class="profile-cancel-button" data-cancel-form>ยกเลิก</button>
                    @if (session('status') === 'password-updated')
                        <span class="profile-status">เปลี่ยนรหัสผ่านแล้ว</span>
                    @endif
                </div>
            </form>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const root = document.querySelector('[data-profile-tabs]');
            const tabs = root.querySelectorAll('[data-tab-target]');
            const panels = root.querySelectorAll('.profile-tab-panel');

            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    tabs.forEach(item => item.classList.remove('is-active'));
                    panels.forEach(panel => panel.classList.add('is-hidden'));
                    tab.classList.add('is-active');
                    document.getElementById(tab.dataset.tabTarget).classList.remove('is-hidden');
                });
            });

            root.querySelectorAll('[data-confirm-form]').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    Swal.fire({
                        icon: 'question',
                        title: 'ยืนยันการแก้ไข',
                        text: form.dataset.confirmForm,
                        showCancelButton: true,
                        confirmButtonText: 'ยืนยัน',
                        cancelButtonText: 'ยกเลิก',
                        confirmButtonColor: '#2563eb',
                        cancelButtonColor: '#94a3b8'
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            form.submit();
                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                            Swal.fire({ icon: 'info', title: 'ยกเลิกการแก้ไขแล้ว', timer: 1600, showConfirmButton: false });
                        }
                    });
                });
            });

            root.querySelectorAll('[data-cancel-form]').forEach(function (button) {
                button.addEventListener('click', function () {
                    Swal.fire({
                        icon: 'info',
                        title: 'ยกเลิกการแก้ไขแล้ว',
                        text: 'ข้อมูลที่ยังไม่ได้บันทึกจะถูกล้างออก',
                        timer: 1800,
                        showConfirmButton: false
                    });
                });
            });

            root.querySelectorAll('[data-password-target]').forEach(function (button) {
                button.addEventListener('click', function () {
                    const input = document.getElementById(button.dataset.passwordTarget);
                    const icon = button.querySelector('i');
                    const isHidden = input.type === 'password';

                    input.type = isHidden ? 'text' : 'password';
                    icon.classList.toggle('fa-eye', isHidden);
                    icon.classList.toggle('fa-eye-slash', !isHidden);
                    button.setAttribute('aria-label', isHidden ? 'ซ่อนรหัสผ่าน' : 'แสดงรหัสผ่าน');
                });
            });

            @if (session('status') === 'two-factor-required')
                Swal.fire({
                    icon: 'warning',
                    title: 'ต้องตั้งค่า 2FA ก่อนใช้งาน',
                    html: '<p>บัญชีผู้ดูแลระบบต้องลงทะเบียน Google Authenticator เพื่อเพิ่มความปลอดภัย</p><p class="mt-2">กดเริ่มตั้งค่า แล้วสแกน QR Code ด้วยแอป Google Authenticator จากนั้นกรอกรหัส 6 หลักเพื่อยืนยัน</p>',
                    confirmButtonText: 'เริ่มตั้งค่า',
                    confirmButtonColor: '#059669',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then(function () {
                    document.getElementById('profile-two-factor').scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            @elseif (session('status') === 'profile-updated')
                Swal.fire({ icon: 'success', title: 'บันทึกข้อมูลสำเร็จ', timer: 2200, showConfirmButton: false });
            @elseif (session('status') === 'password-updated')
                Swal.fire({ icon: 'success', title: 'เปลี่ยนรหัสผ่านสำเร็จ', timer: 2200, showConfirmButton: false });
            @elseif (session('status') === 'two-factor-enabled')
                Swal.fire({ icon: 'success', title: 'เปิดใช้งาน 2FA แล้ว', timer: 2200, showConfirmButton: false });
            @elseif (session('status') === 'two-factor-disabled')
                Swal.fire({ icon: 'success', title: 'ปิดใช้งาน 2FA แล้ว', timer: 2200, showConfirmButton: false });
            @endif
        });
    </script>
</x-app-layout>
