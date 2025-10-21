<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 🔍 Cek kolom sebelum alter table
        if (Schema::hasColumn('lapangan', 'harga_per_jam')) {
            Schema::table('lapangan', function (Blueprint $table) {
                $table->dropColumn('harga_per_jam');
            });
        }

        if (Schema::hasColumn('lapangan', 'fasilitas')) {
            Schema::table('lapangan', function (Blueprint $table) {
                $table->dropColumn('fasilitas');
            });
        }

        if (!Schema::hasColumn('lapangan', 'pemilik_id')) {
            Schema::table('lapangan', function (Blueprint $table) {
                $table->foreignId('pemilik_id')->constrained('users')->onDelete('cascade');
            });
        }

        if (!Schema::hasColumn('lapangan', 'harga_sewa')) {
            Schema::table('lapangan', function (Blueprint $table) {
                $table->decimal('harga_sewa', 10, 2)->nullable();
            });
        }
    }

    public function down()
    {
        if (!Schema::hasColumn('lapangan', 'harga_per_jam')) {
            Schema::table('lapangan', function (Blueprint $table) {
                $table->decimal('harga_per_jam', 10, 2)->nullable();
            });
        }

        if (!Schema::hasColumn('lapangan', 'fasilitas')) {
            Schema::table('lapangan', function (Blueprint $table) {
                $table->text('fasilitas')->nullable();
            });
        }

        if (!Schema::hasColumn('lapangan', 'tiket_tersedia')) {
            Schema::table('lapangan', function (Blueprint $table) {
                $table->integer('tiket_tersedia')->default(0);
            });
        }
    }
};
