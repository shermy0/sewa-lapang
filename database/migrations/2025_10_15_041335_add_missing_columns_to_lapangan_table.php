<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::table('lapangan', function (Blueprint $table) {
            // Pastikan kolom 'harga_sewa' ada sebelum menggunakan after()
            if (!Schema::hasColumn('lapangan', 'harga_sewa')) {
                $table->integer('harga_sewa')->nullable()->after('nama_lapangan');
            }

            // Tambahkan kolom durasi_sewa (default 120 menit)
            if (!Schema::hasColumn('lapangan', 'durasi_sewa')) {
                $table->integer('durasi_sewa')->default(120)->after('harga_sewa');
            }

            // Tambahkan kolom tiket_tersedia
            if (!Schema::hasColumn('lapangan', 'tiket_tersedia')) {
                $table->integer('tiket_tersedia')->default(10)->after('rating');
            }

            // Tambahkan kolom fasilitas
            if (!Schema::hasColumn('lapangan', 'fasilitas')) {
                $table->text('fasilitas')->nullable()->after('tiket_tersedia');
            }

            // Ubah kolom foto menjadi JSON jika sudah ada
            if (Schema::hasColumn('lapangan', 'foto')) {
                $table->json('foto')->nullable()->change();
            } else {
                $table->json('foto')->nullable();
            }
        });
    }

    /**
     * Balikkan migrasi (rollback).
     */
    public function down(): void
    {
        Schema::table('lapangan', function (Blueprint $table) {
            if (Schema::hasColumn('lapangan', 'durasi_sewa')) {
                $table->dropColumn('durasi_sewa');
            }

            if (Schema::hasColumn('lapangan', 'tiket_tersedia')) {
                $table->dropColumn('tiket_tersedia');
            }

            if (Schema::hasColumn('lapangan', 'fasilitas')) {
                $table->dropColumn('fasilitas');
            }

            // Jangan hapus harga_sewa kalau sudah ada di sistem lama
        });
    }
};
