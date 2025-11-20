<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('status', ['active', 'suspended', 'banned'])->default('active')->after('role');
            $table->timestamp('suspended_until')->nullable()->after('status');
            $table->timestamp('banned_until')->nullable()->after('suspended_until');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['status', 'suspended_until', 'banned_until']);
        });
    }
};
