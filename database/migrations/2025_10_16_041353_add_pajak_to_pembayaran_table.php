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
        Schema::table('pembayaran', function (Blueprint $table) {
    $table->decimal('pajak_admin', 12, 2)->default(0);
    $table->decimal('jumlah_bersih', 12, 2)->default(0);
    $table->string('status_pencairan')->default('belum'); // 'belum', 'proses', 'selesai'
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            //
        });
    }
};
