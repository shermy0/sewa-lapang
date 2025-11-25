<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hapus kolom legacy is_scanned & scan_time.
     */
    public function up(): void
    {
        Schema::table('pemesanan', function (Blueprint $table) {
            if (Schema::hasColumn('pemesanan', 'is_scanned')) {
                $table->dropColumn('is_scanned');
            }
            if (Schema::hasColumn('pemesanan', 'scan_time')) {
                $table->dropColumn('scan_time');
            }
        });
    }

    /**
     * Kembalikan kolom jika di-rollback.
     */
    public function down(): void
    {
        Schema::table('pemesanan', function (Blueprint $table) {
            if (!Schema::hasColumn('pemesanan', 'is_scanned')) {
                $table->boolean('is_scanned')->default(false);
            }
            if (!Schema::hasColumn('pemesanan', 'scan_time')) {
                $table->dateTime('scan_time')->nullable();
            }
        });
    }
};
