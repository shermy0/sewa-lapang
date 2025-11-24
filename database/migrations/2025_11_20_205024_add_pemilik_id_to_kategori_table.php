<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('kategori', function (Blueprint $table) {
            if (! Schema::hasColumn('kategori', 'pemilik_id')) {
                $table->foreignId('pemilik_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('users')
                    ->cascadeOnDelete();
            }
        });
    }

    public function down()
    {
        Schema::table('kategori', function (Blueprint $table) {
            $table->dropForeign(['pemilik_id']);
            $table->dropColumn('pemilik_id');
        });
    }
};
