<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lapangan', function (Blueprint $table) {
            if (Schema::hasColumn('lapangan', 'is_suspended')) {
                return;
            }

            $column = $table->boolean('is_suspended')->default(false);

            if (Schema::hasColumn('lapangan', 'status')) {
                $column->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lapangan', function (Blueprint $table) {
            if (Schema::hasColumn('lapangan', 'is_suspended')) {
                $table->dropColumn('is_suspended');
            }
        });
    }
};
