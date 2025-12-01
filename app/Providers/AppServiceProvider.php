<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Midtrans\Config as MidtransConfig; // <── ini penting!

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Gunakan komponen pagination Bootstrap agar ikon panah tidak membesar (Tailwind default)
        Paginator::useBootstrapFive();

        MidtransConfig::$serverKey = env('MIDTRANS_SERVER_KEY');
        MidtransConfig::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        MidtransConfig::$isSanitized = true;
        MidtransConfig::$is3ds = true;

          // Set timezone
    config(['app.locale' => 'id']);
    date_default_timezone_set('Asia/Jakarta');

    // Auto-expire pemesanan
    Pemesanan::where('status', 'menunggu')
        ->where('expires_at', '<', Carbon::now())
        ->update(['status' => 'kadaluarsa']);
    }
}
