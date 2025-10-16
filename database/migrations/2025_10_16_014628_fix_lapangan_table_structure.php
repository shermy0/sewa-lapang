<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('lapangan', function (Blueprint $table) {
            // Hapus kolom yang tidak diperlukan
            if (Schema::hasColumn('lapangan', 'rating')) {
                $table->dropColumn('rating');
            }
            if (Schema::hasColumn('lapangan', 'harga_per_jam')) {
                $table->dropColumn('harga_per_jam');
            }
            if (Schema::hasColumn('lapangan', 'fasilitas')) {
                $table->dropColumn('fasilitas');
            }
            
            // Pastikan kolom yang diperlukan ada
            if (!Schema::hasColumn('lapangan', 'pemilik_id')) {
                $table->foreignId('pemilik_id')->constrained('users')->onDelete('cascade');
            }
            if (!Schema::hasColumn('lapangan', 'tiket_tersedia')) {
                $table->integer('tiket_tersedia')->default(0);
            }
        });
    }

    public function down()
    {
        Schema::table('lapangan', function (Blueprint $table) {
            // Rollback changes if needed
            $table->decimal('rating', 3, 2)->nullable();
            $table->decimal('harga_per_jam', 10, 2)->nullable();
            $table->text('fasilitas')->nullable();
        });
    }
};