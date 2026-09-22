<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
// use PragmaRX\Google2FALaravel\Facade as Google2FA;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        $departments = Department::orderBy('department_name')->get();

        return view('auth.login', compact('departments'));
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // $user = Auth::user();

        // if ($user?->isAdmin() && ! $user->two_factor_enabled) {
        //     $request->session()->put(
        //         'two_factor.setup_secret',
        //         $request->session()->get('two_factor.setup_secret', Google2FA::generateSecretKey())
        //     );

        //     return redirect()->route('profile.edit')->with('status', 'two-factor-required');
        // }

        // if ($user?->two_factor_enabled) {
        //     Auth::logout();

        //     $request->session()->put('two_factor.pending_user_id', $user->id);
        //     $request->session()->put(
        //         'two_factor.intended_url',
        //         $request->session()->pull('url.intended', route('dashboard', absolute: false))
        //     );

        //     return redirect()->route('two-factor.challenge');
        // }

        // $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
