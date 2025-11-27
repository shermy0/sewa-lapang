<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Ubah enum role untuk menambahkan 'petugas'
            $table->enum('role', ['admin', 'pemilik', 'penyewa', 'petugas'])
                  ->default('penyewa')
                  ->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Kembalikan enum seperti semula
            $table->enum('role', ['admin', 'pemilik', 'penyewa'])
                  ->default('penyewa')
                  ->change();
        });
    }
};
