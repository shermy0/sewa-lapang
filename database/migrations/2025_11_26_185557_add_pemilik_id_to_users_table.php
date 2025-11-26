<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Tambahkan kolom pemilik_id, nullable supaya admin atau pemilik sendiri bisa null
            $table->unsignedBigInteger('pemilik_id')->nullable()->after('role');

            // Foreign key ke tabel users (pemilik)
            $table->foreign('pemilik_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['pemilik_id']);
            $table->dropColumn('pemilik_id');
        });
    }
};
