<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\GameCurrency;
use App\Models\GamePackage;
use App\Models\GameTopup;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GameTopup>
 */
class GameTopupFactory extends Factory
{
    protected $model = GameTopup::class;

    public function definition(): array
    {
        $user = User::factory()->create();
        $game = Game::factory()->create();
        $currency = GameCurrency::factory()->create([
            'id_game' => $game->id_game,
        ]);
        $package = GamePackage::factory()->create([
            'id_currency' => $currency->id_currency,
        ]);

        $transaksi = Transaksi::create([
            'jenis_transaksi' => 'topup',
            'jumlah' => $package->amount,
            'harga' => $package->price,
            'status' => 'pending',
            'id_user' => $user->id_user,
            'tanggal_transaksi' => now(),
        ]);

        return [
            'id_user' => $user->id_user,
            'id_game' => $game->id_game,
            'id_currency' => $currency->id_currency,
            'id_package' => $package->id_package,
            'id_transaksi' => $transaksi->id_transaksi,
            'price_idr' => $package->price,
            'payment_method' => 'qris',
            'status' => 'pending',
            'account_data' => [
                'player_id' => '1234567',
            ],
            'payment_meta' => [
                'label' => 'QRIS',
                'type' => 'qris',
            ],
        ];
    }
}
