<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\NewUserRegistered;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Throwable;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration form.
     */
    public function create(): View
    {
        return view('auth.register', [
            'defaultRole' => 'penyewa',
            'notificationEmail' => config('auth.registration_notification_email'),
        ]);
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $defaultRole = 'penyewa';

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $defaultRole,
        ]);

        Auth::login($user);

        if ($address = config('auth.registration_notification_email')) {
            try {
                Mail::to($address)->send(new NewUserRegistered($user));
            } catch (Throwable $e) {
                report($e);
                session()->flash('warning', __('Gagal mengirim notifikasi admin. Silakan periksa konfigurasi email.'));
            }
        }

        try {
            $user->sendEmailVerificationNotification();
        } catch (Throwable $e) {
            report($e);

            $fallbackVerificationUrl = URL::temporarySignedRoute(
                'verification.verify',
                Carbon::now()->addMinutes((int) config('auth.verification.expire', 60)),
                [
                    'id' => $user->getKey(),
                    'hash' => sha1($user->getEmailForVerification()),
                ]
            );

            return redirect()
                ->route('verification.notice')
                ->withErrors([
                    'verification' => __('Email verifikasi gagal dikirim. Silakan coba kirim ulang.'),
                ])
                ->with('fallbackVerificationUrl', $fallbackVerificationUrl);
        }

        return redirect()
            ->route('verification.notice')
            ->with('status', __('Menunggu verifikasi dari Google.'));
    }
}
