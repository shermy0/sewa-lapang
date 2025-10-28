<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemesanan', function (Blueprint $table) {
            if (Schema::hasColumn('pemesanan', 'scan_qr')) {
                $table->dropColumn('scan_qr');
            }
            if (Schema::hasColumn('pemesanan', 'waktu_scan_terakhir')) {
                $table->dropColumn('waktu_scan_terakhir');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pemesanan', function (Blueprint $table) {
            $table->boolean('scan_qr')->default(0);
            $table->dateTime('waktu_scan_terakhir')->nullable();
        });
    }
};
