<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemesanan', function (Blueprint $table) {
            // Tambah kolom baru (jika mau ubah sistem scan)
            if (!Schema::hasColumn('pemesanan', 'scan_qr')) {
                $table->boolean('scan_qr')->default(false)->after('kode_tiket');
            }

            if (!Schema::hasColumn('pemesanan', 'waktu_scan_terakhir')) {
                $table->dateTime('waktu_scan_terakhir')->nullable()->after('scan_qr');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pemesanan', function (Blueprint $table) {
            // Hapus kolom baru saat rollback
            $table->dropColumn(['scan_qr', 'waktu_scan_terakhir']);
        });
    }
};
