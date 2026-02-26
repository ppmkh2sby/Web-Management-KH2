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
        Schema::create('kafarahs', function (Blueprint $table) {
            $table->id();
            // Tidak pakai FK langsung di migration ini karena urutan timestamp
            // lebih awal daripada migration create_santris_table.
            $table->foreignId('santri_id');
            $table->date('tanggal');
            $table->string('status', 20);
            $table->string('kegiatan')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['santri_id', 'tanggal', 'kegiatan']);
            $table->index(['santri_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kafarahs');
    }
};
