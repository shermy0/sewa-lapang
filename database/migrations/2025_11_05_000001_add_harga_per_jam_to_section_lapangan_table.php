<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('section_lapangan', function (Blueprint $table) {
            if (!Schema::hasColumn('section_lapangan', 'harga_per_jam')) {
                $table->decimal('harga_per_jam', 12, 2)->nullable()->after('deskripsi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('section_lapangan', function (Blueprint $table) {
            if (Schema::hasColumn('section_lapangan', 'harga_per_jam')) {
                $table->dropColumn('harga_per_jam');
            }
        });
    }
};
