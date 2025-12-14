<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kegiatans', function (Blueprint $table) {
            $table->id();
            $table->enum('kategori', ['asrama', 'sambung', 'keakraban']);
            $table->enum('waktu', ['subuh', 'pagi', 'siang', 'sore', 'malam']);
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->unique(['kategori', 'waktu']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kegiatans');
    }
};
