<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_temp', function (Blueprint $table) {
            $table->renameColumn('user_id', 'pemilik_id');
        });
    }

    public function down(): void
    {
        Schema::table('cart_temp', function (Blueprint $table) {
            $table->renameColumn('pemilik_id', 'user_id');
        });
    }
};
