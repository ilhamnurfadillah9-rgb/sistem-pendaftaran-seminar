<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('admin.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $email = $request->input('email');

        if (is_string($email)) {
            $request->merge(['email' => mb_strtolower(trim($email))]);
        }

        $credentials = $request->validate(
            [
                'email' => ['required', 'email', 'max:100'],
                'password' => ['required', 'string'],
            ],
            [
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'email.max' => 'Email maksimal 100 karakter.',
                'password.required' => 'Kata sandi wajib diisi.',
                'password.string' => 'Kata sandi tidak valid.',
            ],
        );

        $throttleKey = Str::transliterate(
            Str::lower($credentials['email']).'|'.$request->ip(),
        );

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $availableIn = RateLimiter::availableIn($throttleKey);

            return back()
                ->withErrors([
                    'email' => 'Terlalu banyak percobaan masuk. Coba lagi nanti.',
                ])
                ->onlyInput('email')
                ->setStatusCode(429)
                ->withHeaders(['Retry-After' => (string) $availableIn]);
        }

        if (! Auth::attempt($credentials)) {
            RateLimiter::hit($throttleKey, 60);

            return back()
                ->withErrors(['email' => 'Email atau kata sandi salah.'])
                ->onlyInput('email');
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
