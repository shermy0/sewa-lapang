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
        Schema::table('jadwal_lapangan', function (Blueprint $table) {
            // Hapus foreign key lama ke lapangan (kalau ada)
            if (Schema::hasColumn('jadwal_lapangan', 'lapangan_id')) {
                $table->dropForeign(['lapangan_id']);
                $table->dropColumn('lapangan_id');
            }

            // Tambah relasi baru ke section_lapangan
            $table->foreignId('section_id')->after('id')->constrained('section_lapangan')->onDelete('cascade');

            // Tambahkan kolom baru jika belum ada
            if (!Schema::hasColumn('jadwal_lapangan', 'harga_sewa')) {
                $table->decimal('harga_sewa', 12, 2)->default(0)->after('tersedia');
            }

            if (!Schema::hasColumn('jadwal_lapangan', 'durasi_sewa')) {
                $table->integer('durasi_sewa')->default(60)->after('harga_sewa');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_lapangan', function (Blueprint $table) {
            // Hapus relasi ke section_lapangan
            $table->dropForeign(['section_id']);
            $table->dropColumn('section_id');

            // Tambahkan kembali lapangan_id (kalau di-rollback)
            $table->foreignId('lapangan_id')->constrained('lapangan')->onDelete('cascade');

            // Hapus kolom tambahan
            $table->dropColumn(['harga_sewa', 'durasi_sewa']);
        });
    }
};
