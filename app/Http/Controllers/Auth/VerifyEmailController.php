<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Menangani verifikasi email dan redirect sesuai role.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        // Kalau sudah diverifikasi sebelumnya
        if ($request->user()->hasVerifiedEmail()) {
            return $this->redirectByRole($request->user()->role)
                ->with('status', 'Email sudah diverifikasi.');
        }

        // Tandai email sudah diverifikasi
        $request->fulfill();

        // Arahkan sesuai role
        return $this->redirectByRole($request->user()->role)
            ->with('status', 'Email berhasil diverifikasi!');
    }

    /**
     * Redirect ke halaman sesuai role user.
     */
    private function redirectByRole(string $role): RedirectResponse
    {
        switch ($role) {
            case 'penyewa':
                return redirect()->route('penyewa.beranda');
            case 'pemilik':
                return redirect()->route('dashboard.pemilik');
            case 'admin':
                return redirect()->route('dashboard.admin');
            default:
                return redirect('/');
        }
    }
}
