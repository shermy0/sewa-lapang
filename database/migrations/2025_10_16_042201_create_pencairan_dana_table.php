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
        Schema::create('pencairan_dana', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembayaran_id')->constrained('pembayaran')->onDelete('cascade');
            $table->foreignId('pemilik_id')->constrained('users')->onDelete('cascade');
            $table->string('bank_tujuan');
            $table->string('nomor_rekening');
            $table->string('atas_nama');
            $table->decimal('jumlah', 12, 2);
            $table->string('status')->default('proses'); // proses, sukses, gagal
            $table->string('disbursement_id')->nullable(); // dari Midtrans
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pencairan_dana');
    }
};
