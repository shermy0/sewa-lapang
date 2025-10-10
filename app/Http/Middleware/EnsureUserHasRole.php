<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  array<int, string>|string  $roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $allowedRoles = $this->normalizeRoles($roles);

        if (! in_array($user->role, $allowedRoles, true)) {
            abort(403);
        }

        return $next($request);
    }

    /**
     * Normalize the provided roles into a flat array.
     *
     * @param  array<int, string>  $roles
     * @return array<int, string>
     */
    private function normalizeRoles(array $roles): array
    {
        if (count($roles) === 1 && str_contains($roles[0], '|')) {
            $roles = explode('|', $roles[0]);
        }

        return array_values(array_filter(array_map('trim', $roles), fn ($role) => $role !== ''));
    }
}
