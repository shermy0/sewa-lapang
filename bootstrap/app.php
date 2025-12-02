<?php

use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\AutoExpirePemesanan;
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

        // alias middleware
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
            'active' => EnsureAccountIsActive::class,
        ]);

        // middleware di group web
        $middleware->appendToGroup('web', EnsureAccountIsActive::class);

        // ★★ AUTO EXPIRE PEMESANAN (DITAMBAHKAN DI SINI)
        $middleware->append(AutoExpirePemesanan::class);

        // redirect user setelah login
        RedirectIfAuthenticated::redirectUsing(function ($request) {
            $user = $request->user();

            if (! $user) {
                return route('login');
            }

            return match ($user->role) {
                'penyewa' => route('penyewa.beranda'),
                'pemilik' => route('dashboard.pemilik'),
                'admin' => route('dashboard.admin'),
                'petugas' => route('petugas.index'),
                default => '/',
            };
        });

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
