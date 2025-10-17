<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lapangan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemilik_id')->constrained('users')->onDelete('cascade');
            $table->string('nama_lapangan');
            $table->enum('kategori', ['Badminton', 'Futsal', 'Padel', 'Basket', 'Voli', 'Sepak Bola', 'Lainnya'])->default('Lainnya');
            $table->text('deskripsi')->nullable();
            $table->string('lokasi');
            $table->string('kategori');
            $table->float('rating')->default(0);
            $table->string('foto')->nullable();
            $table->string('status')->default('standard');
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