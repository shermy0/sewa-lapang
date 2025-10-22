<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lapangan', function (Blueprint $table) {
            if (!Schema::hasColumn('lapangan', 'harga_sewa')) {
                $table->decimal('harga_sewa', 10, 2)->nullable();
            }
        });

        if (
            Schema::hasColumn('lapangan', 'harga_per_jam') &&
            Schema::hasColumn('lapangan', 'harga_sewa')
        ) {
            DB::table('lapangan')
                ->whereNull('harga_sewa')
                ->update(['harga_sewa' => DB::raw('harga_per_jam')]);
        }
    }

    public function down(): void
    {
        Schema::table('lapangan', function (Blueprint $table) {
            if (Schema::hasColumn('lapangan', 'harga_sewa')) {
                $table->dropColumn('harga_sewa');
            }
        });
    }
};


