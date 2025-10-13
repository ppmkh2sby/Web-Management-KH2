<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wali_santri_relasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_wali')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_santri')->constrained('users')->onDelete('cascade');
            $table->string('hubungan')->nullable();
            $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wali_santri_relasi');
    }
};
