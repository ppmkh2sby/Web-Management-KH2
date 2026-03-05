<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presensis', function (Blueprint $table) {
            $table->index('updated_at', 'presensis_updated_at_idx');
            $table->index(['sesi_id', 'created_at'], 'presensis_sesi_created_at_idx');
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX IF NOT EXISTS presensis_created_at_legacy_null_sesi_idx ON presensis (created_at) WHERE sesi_id IS NULL');
        }
    }

    public function down(): void
    {
        Schema::table('presensis', function (Blueprint $table) {
            $table->dropIndex('presensis_updated_at_idx');
            $table->dropIndex('presensis_sesi_created_at_idx');
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS presensis_created_at_legacy_null_sesi_idx');
        }
    }
};
