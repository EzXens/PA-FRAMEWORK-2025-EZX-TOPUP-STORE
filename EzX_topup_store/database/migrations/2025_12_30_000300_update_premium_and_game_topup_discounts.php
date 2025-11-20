<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('premiums', function (Blueprint $table) {
            if (! Schema::hasColumn('premiums', 'total_successful_topups')) {
                $table->unsignedInteger('total_successful_topups')->default(0)->after('tanggal_expired');
            }

            $table->unique('id_user');
        });

        Schema::table('game_topups', function (Blueprint $table) {
            if (! Schema::hasColumn('game_topups', 'price_before_discount')) {
                $table->unsignedBigInteger('price_before_discount')->default(0)->after('price_idr');
            }

            if (! Schema::hasColumn('game_topups', 'discount_percentage')) {
                $table->unsignedTinyInteger('discount_percentage')->default(0)->after('price_before_discount');
            }

            if (! Schema::hasColumn('game_topups', 'premium_reward_coins')) {
                $table->unsignedInteger('premium_reward_coins')->default(0)->after('discount_percentage');
            }
        });

        DB::table('game_topups')->update([
            'price_before_discount' => DB::raw('price_idr')
        ]);

        DB::statement("ALTER TABLE transaksi MODIFY COLUMN jenis_transaksi ENUM('topup','purchase','withdraw','premium') DEFAULT 'topup'");
    }

    public function down(): void
    {
        Schema::table('game_topups', function (Blueprint $table) {
            if (Schema::hasColumn('game_topups', 'premium_reward_coins')) {
                $table->dropColumn('premium_reward_coins');
            }

            if (Schema::hasColumn('game_topups', 'discount_percentage')) {
                $table->dropColumn('discount_percentage');
            }

            if (Schema::hasColumn('game_topups', 'price_before_discount')) {
                $table->dropColumn('price_before_discount');
            }
        });

        Schema::table('premiums', function (Blueprint $table) {
            if (Schema::hasColumn('premiums', 'total_successful_topups')) {
                $table->dropColumn('total_successful_topups');
            }

            $table->dropUnique('premiums_id_user_unique');
        });

        DB::statement("ALTER TABLE transaksi MODIFY COLUMN jenis_transaksi ENUM('topup','purchase','withdraw') DEFAULT 'topup'");
    }
};
