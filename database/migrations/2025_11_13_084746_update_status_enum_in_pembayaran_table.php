<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah kolom enum agar mendukung status tambahan
        DB::statement("ALTER TABLE pembayaran MODIFY status ENUM('pending', 'berhasil', 'gagal', 'batal', 'kadaluarsa') DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Kembalikan ke versi sebelumnya jika di-rollback
        DB::statement("ALTER TABLE pembayaran MODIFY status ENUM('pending', 'berhasil', 'gagal') DEFAULT 'pending'");
    }
};
