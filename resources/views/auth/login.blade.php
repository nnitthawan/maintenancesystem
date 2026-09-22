<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.ico') }}">
    <title id="pageTitle">เข้าสู่ระบบ - ระบบแจ้งซ่อมครุภัณฑ์</title>
    <link rel="stylesheet" href="{{ asset('css/lineseed.css') }}">
    <link href="{{ asset('css/login.css') }}" rel="stylesheet">
</head>

<body>
    <div class="login-wrapper">

        <div class="login-logo">
            <div class="logo-icon">
                <img src="{{ asset('favicon.ico') }}" class="login-logo-image" alt="Logo">
            </div>
            <div class="logo-title">ระบบแจ้งซ่อมครุภัณฑ์</div>
            <div class="logo-sub">กลุ่มงานเทคโนโลยีสารสนเทศ</div>
        </div>

        <div class="login-card">
            @if (session('status'))
                <div class="session-status">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="error-msg">
                    {{ $errors->first() }}
                </div>
            @endif

            <div id="loginForm">
                <h1 class="login-heading">เข้าสู่ระบบ</h1>
                <p class="login-description">กรุณากรอกข้อมูลเพื่อเข้าสู่ระบบ</p>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group">
                        <label for="username">ชื่อผู้ใช้</label>
                        <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus
                            autocomplete="username" placeholder="ชื่อผู้ใช้">
                    </div>

                    <div class="form-group">
                        <label for="password">รหัสผ่าน</label>
                        <div class="password-field">
                            <input id="password" type="password" name="password" required autocomplete="current-password"
                                placeholder="รหัสผ่าน">
                            <button type="button" onclick="togglePasswordVisibility('password', 'eyeIconOn', 'eyeIconOff')"
                                class="password-toggle">
                                <svg id="eyeIconOff" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path>
                                    <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path>
                                    <path d="M6.61 6.61A13.52 13.52 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path>
                                    <line x1="2" y1="2" x2="22" y2="22"></line>
                                </svg>
                                <svg id="eyeIconOn" class="is-hidden" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="remember-row justify-between">
                        <div class="rememberpassword">
                            <input id="remember_me" type="checkbox" name="remember">
                            <label for="remember_me">จดจำการเข้าสู่ระบบ</label>
                        </div>
                    </div>

                    <button type="submit" class="btn-login">เข้าสู่ระบบ</button>
                    
                    <div class="form-switch-text">
                        ยังไม่มีบัญชี ? 
                        <button type="button" class="btn-switch-link" onclick="toggleForm('register')">สมัครสมาชิก</button>
                    </div>
                </form>
            </div>

            <div id="registerForm" class="is-hidden">
                <h1 class="login-heading">สมัครสมาชิก</h1>
                <p class="login-description">กรอกข้อมูลเพื่อสร้างบัญชีใหม่</p>

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="form-group">
                        <label for="reg_name">ชื่อ-นามสกุล</label>
                        <input id="reg_name" type="text" name="name" value="{{ old('name') }}" required placeholder="ชื่อ-นามสกุล">
                    </div>

                    <div class="form-group">
                        <label for="reg_username">ชื่อผู้ใช้</label>
                        <input id="reg_username" type="text" name="username" value="{{ old('username') }}" required placeholder="ชื่อผู้ใช้">
                    </div>

                    <div class="form-group">
                        <label for="reg_phone">เบอร์โทรศัพท์</label>
                        <input id="reg_phone" type="tel" name="phone" value="{{ old('phone') }}" required maxlength="10" placeholder="เบอร์โทรศัพท์">
                    </div>

                    <div class="form-group">
                        <label for="reg_department_id">หน่วยงาน / แผนก</label>
                        <select id="reg_department_id" name="department_id" class="department-select" required>
                            <option value="">-- เลือกหน่วยงาน / แผนก --</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->department_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="reg_password">รหัสผ่าน</label>
                        <input id="reg_password" type="password" name="password" required placeholder="รหัสผ่าน">
                    </div>

                    <div class="form-group">
                        <label for="reg_password_confirmation">ยืนยันรหัสผ่าน</label>
                        <input id="reg_password_confirmation" type="password" name="password_confirmation" required placeholder="ยืนยันรหัสผ่าน">
                    </div>

                    <button type="submit" class="btn-register">ยืนยันการสมัครสมาชิก</button>
                    
                    <div class="form-switch-text">
                        มีบัญชีอยู่แล้ว ? 
                        <button type="button" class="btn-switch-link" onclick="toggleForm('login')">เข้าสู่ระบบ</button>
                    </div>
                </form>
            </div>

        </div>

    </div>

    <script>
        // ฟังก์ชันสลับหน้าฟอร์ม และ อัปเดต Title
        function toggleForm(formType) {
            const loginForm = document.getElementById('loginForm');
            const registerForm = document.getElementById('registerForm');
            const pageTitle = document.getElementById('pageTitle');

            if (formType === 'register') {
                loginForm.classList.add('is-hidden');
                registerForm.classList.remove('is-hidden');
                pageTitle.textContent = 'สมัครสมาชิก - ระบบแจ้งซ่อมครุภัณฑ์';
            } else {
                registerForm.classList.add('is-hidden');
                loginForm.classList.remove('is-hidden');
                pageTitle.textContent = 'เข้าสู่ระบบ - ระบบแจ้งซ่อมครุภัณฑ์';
            }
        }

        // ฟังก์ชัน ซ่อน/แสดง รหัสผ่าน
        function togglePasswordVisibility(inputId, eyeOnId, eyeOffId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIconOn = document.getElementById(eyeOnId);
            const eyeIconOff = document.getElementById(eyeOffId);

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIconOn.classList.remove('is-hidden');
                eyeIconOff.classList.add('is-hidden');
            } else {
                passwordInput.type = 'password';
                eyeIconOn.classList.add('is-hidden');
                eyeIconOff.classList.remove('is-hidden');
            }
        }
    </script>
</body>

</html>