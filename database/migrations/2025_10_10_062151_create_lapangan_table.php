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
        Schema::create('lapangan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemilik_id')->constrained('users')->onDelete('cascade');
            $table->string('nama_lapangan');
            $table->enum('kategori', ['Badminton', 'Futsal', 'Padel', 'Basket', 'Voli', 'Sepak Bola', 'Lainnya'])->default('Lainnya');
            $table->text('deskripsi')->nullable();
            $table->string('lokasi');
            $table->decimal('harga_per_jam', 10, 2);
            $table->string('foto')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lapangan');
    }
};