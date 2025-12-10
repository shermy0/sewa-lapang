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

            if (Schema::hasColumn('permintaan_perubahan', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('permintaan_perubahan', 'alasan_internal')) {
                $table->dropColumn('alasan_internal');
            }

            if (Schema::hasColumn('permintaan_perubahan', 'expires_at')) {
                $table->dropColumn('expires_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permintaan_perubahan', function (Blueprint $table) {

            // Tambahkan ulang jika rollback
            if (!Schema::hasColumn('permintaan_perubahan', 'status')) {
                $table->enum('status', ['menunggu', 'disetujui', 'ditolak'])
                      ->default('menunggu');
            }

            if (!Schema::hasColumn('permintaan_perubahan', 'alasan_internal')) {
                $table->text('alasan_internal')->nullable();
            }

            if (!Schema::hasColumn('permintaan_perubahan', 'expires_at')) {
                $table->timestamp('expires_at')->nullable();
            }
        });
    }
};
