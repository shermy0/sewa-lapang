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
        Schema::table('permintaan_perubahan', function (Blueprint $table) {
            // Hapus kolom status
            if (Schema::hasColumn('permintaan_perubahan', 'status')) {
                $table->dropColumn('status');
            }

            // Hapus kolom alasan_internal jika ada
            if (Schema::hasColumn('permintaan_perubahan', 'alasan_internal')) {
                $table->dropColumn('alasan_internal');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permintaan_perubahan', function (Blueprint $table) {

            // Tambahkan kembali kolom status
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak'])
                  ->default('menunggu');

            // Tambahkan kembali kolom alasan_internal
            $table->text('alasan_internal')->nullable();
        });
    }
};
