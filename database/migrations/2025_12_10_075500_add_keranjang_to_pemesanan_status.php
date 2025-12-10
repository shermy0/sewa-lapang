<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tambahkan 'keranjang' ke ENUM status pemesanan
        DB::statement("ALTER TABLE pemesanan MODIFY status ENUM('keranjang', 'menunggu', 'dibayar', 'selesai', 'batal', 'kadaluarsa') DEFAULT 'keranjang'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke versi sebelumnya (tanpa 'keranjang')
        DB::statement("ALTER TABLE pemesanan MODIFY status ENUM('menunggu', 'dibayar', 'selesai', 'batal', 'kadaluarsa') DEFAULT 'menunggu'");
    }
};
