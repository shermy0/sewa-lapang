<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('pemesanan_id')->constrained('pemesanan')->onDelete('cascade');
            $table->decimal('total_harga', 15, 2);
            $table->string('status')->default('selesai'); // contoh: selesai, dibatalkan, pending
            $table->date('tanggal_laporan'); // tanggal transaksi selesai
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan');
    }
};
