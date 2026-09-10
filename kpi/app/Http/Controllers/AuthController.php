<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login', [
            'business' => Business::where('slug', config('kpi.business.slug'))->first(),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'login_code' => ['required', 'string'],
            'pin' => ['required', 'string'],
        ], [
            'login_code.required' => 'Masukkan kod log masuk.',
            'pin.required' => 'Masukkan PIN.',
        ]);

        $ok = Auth::attempt([
            'login_code' => strtolower(trim($data['login_code'])),
            'password' => $data['pin'],
            'is_active' => true,
        ], $request->boolean('remember'));

        if (! $ok) {
            throw ValidationException::withMessages([
                'login_code' => 'Kod atau PIN tidak sah.',
            ]);
        }

        $request->session()->regenerate();
        $user = Auth::user();
        $user->forceFill(['last_login_at' => Carbon::now()])->save();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
