<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tambah opsi status_scan untuk scan GOR (scan_lobby).
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE pemesanan
            MODIFY status_scan ENUM('belum_scan', 'scan_lobby', 'sudah_scan')
            DEFAULT 'belum_scan'
        ");
    }

    /**
     * Kembalikan ke nilai awal (tanpa scan_lobby).
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE pemesanan
            MODIFY status_scan ENUM('belum_scan', 'sudah_scan')
            DEFAULT 'belum_scan'
        ");
    }
};
