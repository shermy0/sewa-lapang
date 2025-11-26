<?php

use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
            'active' => EnsureAccountIsActive::class,
        ]);

        $middleware->appendToGroup('web', EnsureAccountIsActive::class);

        // Atur redirect jika user sudah login tapi mengakses route guest (misal /login, /register)
        RedirectIfAuthenticated::redirectUsing(function ($request) {
            $user = $request->user();

            if (! $user) {
                return route('login');
            }

            return match ($user->role) {
                'penyewa' => route('penyewa.beranda'),
                'pemilik' => route('dashboard.pemilik'),
                'admin' => route('dashboard.admin'),
                'petugas' => route('petugas.dashboard'),
                default => '/',
            };
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
