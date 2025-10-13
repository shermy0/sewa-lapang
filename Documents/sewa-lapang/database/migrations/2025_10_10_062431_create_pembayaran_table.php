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
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemesanan_id')->constrained('pemesanan')->onDelete('cascade');
            $table->string('metode'); // qris, gopay, ovo, dll
            $table->decimal('jumlah', 10, 2);
            $table->enum('status', ['pending', 'berhasil', 'gagal'])->default('pending');
            $table->string('order_id')->unique(); // ID transaksi dari Midtrans / gateway
            $table->string('payment_url')->nullable(); // Link pembayaran
            $table->timestamp('tanggal_pembayaran')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
