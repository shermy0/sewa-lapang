<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Ensure authenticated user has one of the required roles.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->getAttribute('status') === 'nonaktif') {
            abort(403, 'Akun dinonaktifkan. Silakan hubungi administrator.');
        }

        if ($roles !== [] && !in_array($user->role, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
