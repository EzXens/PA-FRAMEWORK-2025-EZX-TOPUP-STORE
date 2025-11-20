<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_topups', function (Blueprint $table) {
            $table->text('rejection_reason')->nullable()->after('rejected_at');
        });

        Schema::table('coin_purchases', function (Blueprint $table) {
            $table->text('rejection_reason')->nullable()->after('rejected_at');
        });
    }

    public function down(): void
    {
        Schema::table('coin_purchases', function (Blueprint $table) {
            $table->dropColumn('rejection_reason');
        });

        Schema::table('game_topups', function (Blueprint $table) {
            $table->dropColumn('rejection_reason');
        });
    }
};
