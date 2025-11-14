<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('role_verification_codes', function (Blueprint $table) {
            $table->unique('role');
            $table->index('expires_at');
        });
    }
    public function down(): void {
        Schema::table('role_verification_codes', function (Blueprint $table) {
            $table->dropUnique(['role']);
            $table->dropIndex(['expires_at']);
        });
    }
};
