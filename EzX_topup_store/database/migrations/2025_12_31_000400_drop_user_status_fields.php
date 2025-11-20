<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = array_values(array_filter([
                'status',
                'suspended_until',
                'banned_until',
            ], fn (string $column) => Schema::hasColumn('users', $column)));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'status')) {
                $table->enum('status', ['active', 'suspended', 'banned'])->default('active')->after('role');
            }

            if (! Schema::hasColumn('users', 'suspended_until')) {
                $table->timestamp('suspended_until')->nullable()->after('status');
            }

            if (! Schema::hasColumn('users', 'banned_until')) {
                $table->timestamp('banned_until')->nullable()->after('suspended_until');
            }
        });
    }
};
