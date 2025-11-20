<?php

namespace Database\Factories;

use App\Models\CoinPurchase;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CoinPurchase>
 */
class CoinPurchaseFactory extends Factory
{
    protected $model = CoinPurchase::class;

    public function definition(): array
    {
        $price = fake()->randomElement([10000, 25000, 50000, 100000]);
        $coinAmount = (int) ($price / 100);
        $packageKey = match ($price) {
            25000 => '25k',
            50000 => '50k',
            100000 => '100k',
            default => '10k',
        };

        return [
            'id_user' => User::factory(),
            'id_transaksi' => null,
            'package_key' => $packageKey,
            'coin_amount' => $coinAmount,
            'price_idr' => $price,
            'payment_method' => fake()->randomElement(['qris', 'bca', 'dana']),
            'status' => 'pending',
            'payment_meta' => null,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (CoinPurchase $purchase) {
            if (! $purchase->id_transaksi) {
                $transaksi = Transaksi::create([
                    'jenis_transaksi' => 'purchase',
                    'jumlah' => $purchase->coin_amount,
                    'harga' => $purchase->price_idr,
                    'status' => 'pending',
                    'id_user' => $purchase->id_user,
                    'tanggal_transaksi' => now(),
                ]);

                $purchase->update(['id_transaksi' => $transaksi->id_transaksi]);
            }

            if (! $purchase->payment_meta) {
                $purchase->update([
                    'payment_meta' => [
                        'label' => strtoupper($purchase->payment_method),
                        'type' => in_array($purchase->payment_method, ['bca']) ? 'bank_transfer' : ($purchase->payment_method === 'qris' ? 'qris' : 'ewallet'),
                    ],
                ]);
            }
        });
    }
}
