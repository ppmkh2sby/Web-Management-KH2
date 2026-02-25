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
        Schema::table('kafarahs', function (Blueprint $table) {
            // Drop old unique constraint
            $table->dropUnique(['santri_id', 'tanggal', 'kegiatan']);
            
            // Drop old columns
            $table->dropColumn(['status', 'kegiatan', 'keterangan']);
            
            // Add new columns
            $table->string('jenis_pelanggaran')->after('tanggal');
            $table->string('kafarah')->after('jenis_pelanggaran');
            $table->integer('jumlah_setor')->default(0)->after('kafarah');
            $table->integer('tanggungan')->default(0)->after('jumlah_setor');
            $table->text('tenggat')->nullable()->after('tanggungan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kafarahs', function (Blueprint $table) {
            // Drop new columns
            $table->dropColumn(['jenis_pelanggaran', 'kafarah', 'jumlah_setor', 'tanggungan', 'tenggat']);
            
            // Restore old columns
            $table->string('status', 20)->after('tanggal');
            $table->string('kegiatan')->nullable()->after('status');
            $table->text('keterangan')->nullable()->after('kegiatan');
            
            // Restore old unique constraint
            $table->unique(['santri_id', 'tanggal', 'kegiatan']);
        });
    }
};
