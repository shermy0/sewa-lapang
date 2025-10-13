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
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemesanan');
    }
};
