<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permintaan_perubahan', function (Blueprint $table) {
            if (!Schema::hasColumn('permintaan_perubahan', 'section_lama_id')) {
                $table->foreignId('section_lama_id')
                    ->nullable()
                    ->after('pemesanan_id')
                    ->constrained('section_lapangan')
                    ->cascadeOnDelete();
            }

            if (!Schema::hasColumn('permintaan_perubahan', 'section_baru_id')) {
                $table->foreignId('section_baru_id')
                    ->nullable()
                    ->after('section_lama_id')
                    ->constrained('section_lapangan')
                    ->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('permintaan_perubahan', function (Blueprint $table) {
            if (Schema::hasColumn('permintaan_perubahan', 'section_baru_id')) {
                $table->dropForeign(['section_baru_id']);
                $table->dropColumn('section_baru_id');
            }

            if (Schema::hasColumn('permintaan_perubahan', 'section_lama_id')) {
                $table->dropForeign(['section_lama_id']);
                $table->dropColumn('section_lama_id');
            }
        });
    }
};
