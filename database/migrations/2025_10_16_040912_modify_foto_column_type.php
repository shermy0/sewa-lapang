<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('lapangan', function (Blueprint $table) {
            $table->json('foto')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('lapangan', function (Blueprint $table) {
            $table->text('foto')->nullable()->change();
        });
    }
};
