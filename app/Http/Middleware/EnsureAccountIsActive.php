<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->status === 'nonaktif') {
            $email = $user->email;

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('banding.create', ['email' => $email])
                ->with('error', __('Akun Anda sedang diblokir. Ajukan banding untuk dipertimbangkan kembali.'))
                ->withInput(['email' => $email]);
        }

        return $next($request);
    }
}
