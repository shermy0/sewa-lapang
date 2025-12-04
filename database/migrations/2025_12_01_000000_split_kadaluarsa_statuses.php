<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE pemesanan MODIFY status ENUM('menunggu', 'dibayar', 'selesai', 'batal', 'kadaluarsa', 'kadaluarsa_pemesanan') DEFAULT 'menunggu'");
        DB::statement("UPDATE pemesanan SET status = 'kadaluarsa_pemesanan' WHERE status = 'kadaluarsa'");

        DB::statement("ALTER TABLE pembayaran MODIFY status ENUM('pending', 'berhasil', 'gagal', 'batal', 'kadaluarsa', 'kadaluarsa_pembayaran') DEFAULT 'pending'");
        DB::statement("UPDATE pembayaran SET status = 'kadaluarsa_pembayaran' WHERE status = 'kadaluarsa'");
    }

    public function down(): void
    {
        DB::statement("UPDATE pemesanan SET status = 'kadaluarsa' WHERE status = 'kadaluarsa_pemesanan'");
        DB::statement("ALTER TABLE pemesanan MODIFY status ENUM('menunggu', 'dibayar', 'selesai', 'batal', 'kadaluarsa') DEFAULT 'menunggu'");

        DB::statement("UPDATE pembayaran SET status = 'kadaluarsa' WHERE status = 'kadaluarsa_pembayaran'");
        DB::statement("ALTER TABLE pembayaran MODIFY status ENUM('pending', 'berhasil', 'gagal', 'batal', 'kadaluarsa') DEFAULT 'pending'");
    }
};
