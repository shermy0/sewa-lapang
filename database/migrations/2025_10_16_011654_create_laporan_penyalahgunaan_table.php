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
        Schema::create('laporan_penyalahgunaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelapor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('terlapor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('lapangan_id')->nullable()->constrained('lapangan')->nullOnDelete();
            $table->string('kategori')->nullable();
            $table->text('deskripsi');
            $table->enum('status', ['pending', 'diproses', 'ditutup'])->default('pending');
            $table->text('catatan_admin')->nullable();
            $table->foreignId('ditangani_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('ditangani_pada')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_penyalahgunaan');
    }
};
