<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lapangan', function (Blueprint $table) {
            $table->dropColumn(['tiket_tersedia', 'status', 'is_verified']);
        });
    }

    public function down(): void
    {
        Schema::table('lapangan', function (Blueprint $table) {
            $table->integer('tiket_tersedia')->default(0)->comment('Jumlah tiket yang tersedia untuk booking');
            $table->string('status')->default('standard');
            $table->boolean('is_verified')->default(false);
        });
    }
};
