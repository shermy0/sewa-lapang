<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Long list enum dulu supaya update nilai tidak gagal
        DB::statement("
            ALTER TABLE pemesanan
            MODIFY status_scan ENUM('belum_scan','scan_lobby','sudah_scan','masuk_arena','masuk_lapang') DEFAULT 'belum_scan'
        ");

        // 2) Alihkan nilai lama ke label baru
        DB::statement("UPDATE pemesanan SET status_scan = 'masuk_arena' WHERE status_scan = 'scan_lobby'");
        DB::statement("UPDATE pemesanan SET status_scan = 'masuk_lapang' WHERE status_scan = 'sudah_scan'");

        // 3) Kunci enum ke label baru saja
        DB::statement("
            ALTER TABLE pemesanan
            MODIFY status_scan ENUM('belum_scan', 'masuk_arena', 'masuk_lapang') DEFAULT 'belum_scan'
        ");
    }

    public function down(): void
    {
        // 1) Long list enum supaya update nilai aman
        DB::statement("
            ALTER TABLE pemesanan
            MODIFY status_scan ENUM('belum_scan','scan_lobby','sudah_scan','masuk_arena','masuk_lapang') DEFAULT 'belum_scan'
        ");

        // 2) Kembalikan nilai ke label lama
        DB::statement("UPDATE pemesanan SET status_scan = 'scan_lobby' WHERE status_scan = 'masuk_arena'");
        DB::statement("UPDATE pemesanan SET status_scan = 'sudah_scan' WHERE status_scan = 'masuk_lapang'");

        // 3) Kunci enum ke label lama
        DB::statement("
            ALTER TABLE pemesanan
            MODIFY status_scan ENUM('belum_scan', 'scan_lobby', 'sudah_scan') DEFAULT 'belum_scan'
        ");
    }
};
