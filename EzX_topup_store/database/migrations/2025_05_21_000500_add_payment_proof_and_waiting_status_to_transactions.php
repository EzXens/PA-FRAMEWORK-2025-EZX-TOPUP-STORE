<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_topups', function (Blueprint $table) {
            $table->string('payment_proof_path')->nullable()->after('payment_meta');
        });

        Schema::table('coin_purchases', function (Blueprint $table) {
            $table->string('payment_proof_path')->nullable()->after('payment_meta');
        });

        DB::statement("ALTER TABLE game_topups MODIFY COLUMN status ENUM('pending','waiting_verification','approved','rejected') DEFAULT 'pending'");
        DB::statement("ALTER TABLE coin_purchases MODIFY COLUMN status ENUM('pending','waiting_verification','approved','rejected') DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE game_topups MODIFY COLUMN status ENUM('pending','approved','rejected') DEFAULT 'pending'");
        DB::statement("ALTER TABLE coin_purchases MODIFY COLUMN status ENUM('pending','approved','rejected') DEFAULT 'pending'");

        Schema::table('game_topups', function (Blueprint $table) {
            $table->dropColumn('payment_proof_path');
        });

        Schema::table('coin_purchases', function (Blueprint $table) {
            $table->dropColumn('payment_proof_path');
        });
    }
};
