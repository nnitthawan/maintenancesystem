<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PragmaRX\Google2FALaravel\Facade as Google2FA;

class TwoFactorController extends Controller
{
    public function challenge(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('two_factor.pending_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function verify(Request $request): RedirectResponse
    {
        $pendingUserId = $request->session()->get('two_factor.pending_user_id');
        $throttleKey = Str::lower("two-factor|{$pendingUserId}|{$request->ip()}");

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'code' => 'ลองรหัสยืนยันบ่อยเกินไป กรุณารอสักครู่แล้วลองใหม่',
            ]);
        }

        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = \App\Models\User::find($pendingUserId);

        if (! $user || ! $user->two_factor_enabled || ! $user->two_factor_secret) {
            $request->session()->forget(['two_factor.pending_user_id', 'two_factor.intended_url']);

            return redirect()->route('login')->withErrors([
                'username' => 'ไม่สามารถยืนยันตัวตนแบบสองขั้นตอนได้',
            ]);
        }

        $timestamp = Google2FA::verifyKeyNewer(
            $user->two_factor_secret,
            $request->string('code')->toString(),
            $user->two_factor_last_used_at
        );

        if (! $timestamp) {
            RateLimiter::hit($throttleKey, 300);

            throw ValidationException::withMessages([
                'code' => 'รหัสยืนยันไม่ถูกต้องหรือถูกใช้งานไปแล้ว',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $user->forceFill(['two_factor_last_used_at' => $timestamp])->save();
        $intendedUrl = $request->session()->pull('two_factor.intended_url');
        $request->session()->forget('two_factor.pending_user_id');
        Auth::login($user, false);
        $request->session()->regenerate();

        return redirect()->to($intendedUrl ?: route('dashboard', absolute: false));
    }

    public function cancel(Request $request): RedirectResponse
    {
        $request->session()->forget(['two_factor.pending_user_id', 'two_factor.intended_url']);

        return redirect()->route('login');
    }

    public function enable(Request $request): RedirectResponse
    {
        $secret = Google2FA::generateSecretKey();
        $request->session()->put('two_factor.setup_secret', $secret);

        return redirect()->route('profile.edit')->with('status', 'two-factor-setup');
    }

    public function confirm(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $secret = $request->session()->get('two_factor.setup_secret');
        if (! $secret || ! Google2FA::verifyKey($secret, $request->string('code')->toString())) {
            throw ValidationException::withMessages([
                'two_factor' => 'รหัสยืนยันไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง',
            ]);
        }

        $request->user()->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
            'two_factor_last_used_at' => null,
        ])->save();
        $request->session()->forget('two_factor.setup_secret');

        return redirect()->route('profile.edit')->with('status', 'two-factor-enabled');
    }

    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $request->user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_enabled' => false,
            'two_factor_confirmed_at' => null,
            'two_factor_last_used_at' => null,
        ])->save();
        $request->session()->forget('two_factor.setup_secret');

        return redirect()->route('profile.edit')->with('status', 'two-factor-disabled');
    }
}
