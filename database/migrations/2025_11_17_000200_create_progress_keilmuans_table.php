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
        Schema::create('progress_keilmuans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained()->cascadeOnDelete();
            $table->string('judul');
            $table->unsignedInteger('target')->default(0);
            $table->unsignedInteger('capaian')->default(0);
            $table->string('satuan', 30)->nullable();
            $table->string('level', 50)->nullable();
            $table->string('pembimbing', 100)->nullable();
            $table->text('catatan')->nullable();
            $table->date('terakhir_setor')->nullable();
            $table->timestamps();

            $table->index(['santri_id', 'judul']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress_keilmuans');
    }
};
