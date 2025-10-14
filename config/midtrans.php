<?php

return [

    /*
    |--------------------------------------------------------------------------
    | MIDTRANS CONFIGURATION
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk menghubungkan aplikasi Laravel kamu dengan Midtrans.
    | Pastikan server key & client key sudah diisi di file .env.
    |
    */

    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'is_sanitized' => true,
    'is_3ds' => true,
    'client_key' => env('MIDTRANS_CLIENT_KEY'),

    // Optional: URL base API Midtrans
    'base_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://api.midtrans.com/v2/'
        : 'https://api.sandbox.midtrans.com/v2/',

    // Optional: Timeout (detik)
    'timeout' => 30,

    // Optional: Logging untuk debug
    'log_enabled' => true,

    // Path file log
    'log_file' => storage_path('logs/midtrans.log'),

];
