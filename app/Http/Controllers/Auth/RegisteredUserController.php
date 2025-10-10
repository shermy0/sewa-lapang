<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\NewUserRegistered;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
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
            'roles' => $this->availableRoles(),
            'notificationEmail' => config('auth.registration_notification_email'),
        ]);
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $roles = $this->availableRoles();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::in($roles)],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
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

            return redirect()
                ->route('verification.notice')
                ->withErrors([
                    'verification' => __('Email verifikasi gagal dikirim. Silakan coba kirim ulang.'),
                ]);
        }

        return redirect()
            ->route('verification.notice')
            ->with('status', __('Menunggu verifikasi dari Google.'));
    }

    /**
     * Resolve registerable roles from the configuration.
     *
     * @return array<int, string>
     */
    private function availableRoles(): array
    {
        $roles = $this->fetchRolesFromDatabase();

        if (empty($roles)) {
            $roles = config('auth.register_roles', []);
        }

        if (! is_array($roles) || empty($roles)) {
            $roles = ['admin', 'pemilik', 'penyewa'];
        }

        return array_values(array_filter($roles, fn ($role) => is_string($role) && $role !== ''));
    }

    /**
     * Attempt to read enum values from the users.role column.
     *
     * @return array<int, string>
     */
    private function fetchRolesFromDatabase(): array
    {
        try {
            $column = DB::selectOne("SHOW COLUMNS FROM users WHERE Field = 'role'");

            if (! $column || ! isset($column->Type)) {
                return [];
            }

            if (preg_match("/^enum\((.*)\)$/", $column->Type, $matches)) {
                return array_map(
                    fn ($value) => trim($value, "'"),
                    explode(',', $matches[1])
                );
            }
        } catch (Throwable $e) {
            report($e);
        }

        return [];
    }
}
