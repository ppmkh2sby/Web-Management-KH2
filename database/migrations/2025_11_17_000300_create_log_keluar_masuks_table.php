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
        Schema::create('log_keluar_masuks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained()->cascadeOnDelete();
            $table->date('tanggal_pengajuan');
            $table->string('jenis');
            $table->string('rentang')->nullable();
            $table->string('status', 30)->default('proses');
            $table->string('petugas')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['santri_id', 'tanggal_pengajuan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_keluar_masuks');
    }
};
