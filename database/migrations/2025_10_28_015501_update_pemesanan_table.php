<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemesanan', function (Blueprint $table) {
            // Hapus kolom lama jika ada
            if (Schema::hasColumn('pemesanan', 'is_scanned')) {
                $table->dropColumn('is_scanned');
            }
            if (Schema::hasColumn('pemesanan', 'scan_time')) {
                $table->dropColumn('scan_time');
            }

            // Tambahkan kolom baru
            $table->enum('status_scan', ['belum_scan', 'sudah_scan'])->default('belum_scan')->after('kode_tiket');
            $table->dateTime('waktu_scan')->nullable()->after('status_scan');
        });
    }

    public function down(): void
    {
        Schema::table('pemesanan', function (Blueprint $table) {
            // Kembalikan ke struktur semula (kalau dibutuhkan rollback)
            $table->dropColumn(['status_scan', 'waktu_scan']);
            $table->boolean('is_scanned')->default(0);
            $table->timestamp('scan_time')->nullable();
        });
    }
};
