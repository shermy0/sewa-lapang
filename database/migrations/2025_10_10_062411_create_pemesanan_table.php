<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pemesanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penyewa_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('lapangan_id')->constrained('lapangan')->onDelete('cascade');
            $table->foreignId('jadwal_id')->constrained('jadwal_lapangan')->onDelete('cascade');
            $table->enum('status', ['menunggu', 'dibayar', 'selesai', 'batal'])->default('menunggu');
            $table->string('kode_tiket')->nullable()->comment('Kode unik untuk tiket/barcode');
            $table->enum('status_scan', ['belum_scan', 'sudah_scan'])->default('belum_scan');
            $table->dateTime('waktu_scan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {           
         $table->dropColumn(['is_scanned', 'scan_time', 'kode_tiket']);
        Schema::dropIfExists('pemesanan');
    }
};
