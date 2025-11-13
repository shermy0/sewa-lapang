<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banner', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 100)->nullable();
            $table->string('gambar', 255);
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banner');
    }
};