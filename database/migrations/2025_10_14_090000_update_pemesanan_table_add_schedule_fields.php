<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pemesanan')) {
            Schema::table('pemesanan', function (Blueprint $table) {
                if (! Schema::hasColumn('pemesanan', 'tanggal')) {
                    $table->date('tanggal')->nullable()->after('lapangan_id');
                }

                if (! Schema::hasColumn('pemesanan', 'jam_mulai')) {
                    $table->time('jam_mulai')->nullable()->after('tanggal');
                }

                if (! Schema::hasColumn('pemesanan', 'jam_selesai')) {
                    $table->time('jam_selesai')->nullable()->after('jam_mulai');
                }

                if (! Schema::hasColumn('pemesanan', 'total_harga')) {
                    $table->decimal('total_harga', 12, 2)->nullable()->after('status');
                }
            });

            if (Schema::hasColumn('pemesanan', 'jadwal_id')) {
                Schema::table('pemesanan', function (Blueprint $table) {
                    $table->dropForeign(['jadwal_id']);
                    $table->dropColumn('jadwal_id');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pemesanan')) {
            Schema::table('pemesanan', function (Blueprint $table) {
                if (Schema::hasColumn('pemesanan', 'total_harga')) {
                    $table->dropColumn('total_harga');
                }

                if (Schema::hasColumn('pemesanan', 'jam_selesai')) {
                    $table->dropColumn('jam_selesai');
                }

                if (Schema::hasColumn('pemesanan', 'jam_mulai')) {
                    $table->dropColumn('jam_mulai');
                }

                if (Schema::hasColumn('pemesanan', 'tanggal')) {
                    $table->dropColumn('tanggal');
                }

                if (! Schema::hasColumn('pemesanan', 'jadwal_id')) {
                    $table->foreignId('jadwal_id')->nullable()->constrained('jadwal_lapangan')->nullOnDelete()->after('lapangan_id');
                }
            });
        }
    }
};

