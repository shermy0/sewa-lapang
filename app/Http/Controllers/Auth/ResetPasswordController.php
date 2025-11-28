<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class ResetPasswordController extends Controller
{
    /**
     * Show reset password form
     */
    public function showResetForm(Request $request, string $token = null)
    {
        return view('auth.passwords.reset', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Handle reset password
     */
    public function reset(Request $request)
    {
        // Validasi input
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => [
                'required',
                'confirmed',
                PasswordRule::min(8)
            ],
        ]);

        // Proses reset password
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // Jika berhasil reset password
        if ($status === Password::PASSWORD_RESET) {

            // Bahasa Indonesia
            return redirect()
                ->route('login')
                ->with('status', 'Kata sandi Anda berhasil diubah Silakan login.');
        }

        // Jika gagal
        return back()->withErrors([
            'email' => 'Token tidak valid atau email tidak ditemukan.',
        ]);
    }
}
