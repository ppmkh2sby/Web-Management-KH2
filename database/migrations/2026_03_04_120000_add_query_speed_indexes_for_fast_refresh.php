<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presensis', function (Blueprint $table) {
            $table->index(['sesi_id', 'santri_id'], 'presensis_sesi_santri_idx');
            $table->index(['kegiatan_id', 'waktu'], 'presensis_kegiatan_waktu_idx');
            $table->index(['santri_id', 'created_at'], 'presensis_santri_created_idx2');
        });

        Schema::table('santris', function (Blueprint $table) {
            $table->index('gender', 'santris_gender_idx');
            $table->index(['gender', 'kelas_id'], 'santris_gender_kelas_idx');
        });

        Schema::table('sesi', function (Blueprint $table) {
            $table->index('tanggal', 'sesis_tanggal_idx');
        });

        Schema::table('kegiatans', function (Blueprint $table) {
            $table->index(['kategori', 'waktu'], 'kegiatans_kategori_waktu_idx');
        });

        Schema::table('log_keluar_masuks', function (Blueprint $table) {
            $table->index(['santri_id', 'tanggal_pengajuan'], 'log_santri_tanggal_idx');
        });

        Schema::table('progress_keilmuans', function (Blueprint $table) {
            $table->index(['level', 'santri_id'], 'progress_level_santri_idx');
        });
    }

    public function down(): void
    {
        Schema::table('presensis', function (Blueprint $table) {
            $table->dropIndex('presensis_sesi_santri_idx');
            $table->dropIndex('presensis_kegiatan_waktu_idx');
            $table->dropIndex('presensis_santri_created_idx2');
        });

        Schema::table('santris', function (Blueprint $table) {
            $table->dropIndex('santris_gender_idx');
            $table->dropIndex('santris_gender_kelas_idx');
        });

        Schema::table('sesi', function (Blueprint $table) {
            $table->dropIndex('sesis_tanggal_idx');
        });

        Schema::table('kegiatans', function (Blueprint $table) {
            $table->dropIndex('kegiatans_kategori_waktu_idx');
        });

        Schema::table('log_keluar_masuks', function (Blueprint $table) {
            $table->dropIndex('log_santri_tanggal_idx');
        });

        Schema::table('progress_keilmuans', function (Blueprint $table) {
            $table->dropIndex('progress_level_santri_idx');
        });
    }
};
