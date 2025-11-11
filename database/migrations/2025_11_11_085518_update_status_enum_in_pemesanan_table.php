<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ubah tipe enum agar menambah opsi 'kadaluarsa'
        DB::statement("ALTER TABLE pemesanan MODIFY status ENUM('menunggu', 'dibayar', 'selesai', 'batal', 'kadaluarsa') DEFAULT 'menunggu'");
    }

    public function down(): void
    {
        // rollback: hapus opsi 'kadaluarsa' kalau dibatalkan
        DB::statement("ALTER TABLE pemesanan MODIFY status ENUM('menunggu', 'dibayar', 'selesai', 'batal') DEFAULT 'menunggu'");
    }
};
