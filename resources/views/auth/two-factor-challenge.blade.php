<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ยืนยันตัวตน - ระบบแจ้งซ่อมครุภัณฑ์</title>
    <link rel="stylesheet" href="{{ asset('css/lineseed.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-logo">
            <div class="logo-icon">
                <img src="{{ asset('favicon.ico') }}" class="login-logo-image" alt="Logo">
            </div>
            <div class="logo-title">ยืนยันตัวตนสองขั้นตอน</div>
            <div class="logo-sub">เปิด Google Authenticator เพื่อดูรหัส 6 หลัก</div>
        </div>

        <div class="login-card">
            @if ($errors->any())
                <div class="error-msg">{{ $errors->first() }}</div>
            @endif

            <h1 class="login-heading">กรอกรหัสยืนยัน</h1>
            <p class="login-description">กรอกรหัสปัจจุบันจาก Google Authenticator เพื่อเข้าสู่ระบบ</p>

            <form method="POST" action="{{ route('two-factor.verify') }}">
                @csrf
                <div class="form-group">
                    <label for="code">รหัสยืนยัน 6 หลัก</label>
                    <input id="code" type="text" name="code" inputmode="numeric" pattern="[0-9]{6}"
                        maxlength="6" autocomplete="one-time-code" required autofocus placeholder="000000">
                </div>
                <button type="submit" class="btn-login">ยืนยันและเข้าสู่ระบบ</button>
            </form>

            <form method="POST" action="{{ route('two-factor.cancel') }}" class="mt-4 text-center">
                @csrf
                <button type="submit" class="btn-switch-link">ยกเลิก</button>
            </form>
        </div>
    </div>
</body>
</html>
