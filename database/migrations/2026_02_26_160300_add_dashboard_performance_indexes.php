<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presensis', function (Blueprint $table) {
            $table->index('status', 'presensis_status_idx');
            $table->index('created_at', 'presensis_created_at_idx');
            $table->index(['santri_id', 'status'], 'presensis_santri_status_idx');
        });

        Schema::table('progress_keilmuans', function (Blueprint $table) {
            $table->index(['santri_id', 'updated_at'], 'progress_santri_updated_idx');
            $table->index('level', 'progress_level_idx');
        });

        Schema::table('log_keluar_masuks', function (Blueprint $table) {
            $table->index('tanggal_pengajuan', 'log_tanggal_pengajuan_idx');
            $table->index('status', 'log_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('presensis', function (Blueprint $table) {
            $table->dropIndex('presensis_status_idx');
            $table->dropIndex('presensis_created_at_idx');
            $table->dropIndex('presensis_santri_status_idx');
        });

        Schema::table('progress_keilmuans', function (Blueprint $table) {
            $table->dropIndex('progress_santri_updated_idx');
            $table->dropIndex('progress_level_idx');
        });

        Schema::table('log_keluar_masuks', function (Blueprint $table) {
            $table->dropIndex('log_tanggal_pengajuan_idx');
            $table->dropIndex('log_status_idx');
        });
    }
};
