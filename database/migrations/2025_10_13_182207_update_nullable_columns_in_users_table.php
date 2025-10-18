<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = ['country', 'state', 'city', 'address', 'zip'];

            foreach ($columns as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->string($col)->nullable()->change();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = ['country', 'state', 'city', 'address', 'zip'];

            foreach ($columns as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->string($col)->nullable(false)->change();
                }
            }
        });
    }
};
