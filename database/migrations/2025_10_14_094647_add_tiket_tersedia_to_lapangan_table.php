<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('lapangan', function (Blueprint $table) {
            if (Schema::hasColumn('lapangan', 'tiket_tersedia')) {
                return;
            }

            $afterColumn = null;

            if (Schema::hasColumn('lapangan', 'harga_sewa')) {
                $afterColumn = 'harga_sewa';
            } elseif (Schema::hasColumn('lapangan', 'harga_per_jam')) {
                $afterColumn = 'harga_per_jam';
            } elseif (Schema::hasColumn('lapangan', 'kategori')) {
                $afterColumn = 'kategori';
            }

            $column = $table->integer('tiket_tersedia')
                ->default(0)
                ->comment('Jumlah tiket yang tersedia untuk booking');

            if ($afterColumn) {
                $column->after($afterColumn);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('lapangan', function (Blueprint $table) {
            $table->dropColumn('tiket_tersedia');
        });
    }
};
