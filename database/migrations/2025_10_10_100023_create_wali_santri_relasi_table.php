<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('wali_santri_relasi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_wali');
            $table->unsignedBigInteger('id_santri');
            $table->string('hubungan')->nullable(); // contoh: ayah, ibu, wali
            $table->timestamps();

            $table->foreign('id_wali')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_santri')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('wali_santri_relasi');
    }
};
