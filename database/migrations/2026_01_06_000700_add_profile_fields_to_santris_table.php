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
        Schema::table('santris', function (Blueprint $table) {
            $table->string('kampus', 100)->nullable()->after('tim');
            $table->string('jurusan', 150)->nullable()->after('kampus');
            $table->string('gender', 10)->nullable()->after('jurusan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('santris', function (Blueprint $table) {
            $table->dropColumn(['kampus', 'jurusan', 'gender']);
        });
    }
};
