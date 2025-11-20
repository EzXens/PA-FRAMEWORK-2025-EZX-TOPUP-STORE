<?php

namespace Database\Factories;

use App\Models\GamePackage;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<TransaksiDetail>
 */
class TransaksiDetailFactory extends Factory
{
    protected $model = TransaksiDetail::class;

    public function definition(): array
    {
        $date = Carbon::now()->subDays(fake()->numberBetween(0, 60))->setTimeFromTimeString(fake()->time());

        return [
            'jenis_transaksi' => fake()->randomElement(['topup', 'purchase', 'withdraw']),
            'jumlah' => fake()->numberBetween(1, 5),
            'tanggal_transaksi' => $date,
            'harga' => fake()->randomFloat(2, 1, 500),
            'id_transaksi' => Transaksi::factory(),
            'id_package' => GamePackage::factory(),
        ];
    }
}
