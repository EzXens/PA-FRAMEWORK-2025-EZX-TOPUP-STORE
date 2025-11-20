<?php

namespace Database\Factories;

use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Transaksi>
 */
class TransaksiFactory extends Factory
{
    protected $model = Transaksi::class;

    public function definition(): array
    {
        $date = Carbon::now()->subDays(fake()->numberBetween(0, 60))->setTimeFromTimeString(fake()->time());

        return [
            'jenis_transaksi' => fake()->randomElement(['topup', 'purchase', 'withdraw']),
            'jumlah' => fake()->numberBetween(1, 10),
            'tanggal_transaksi' => $date,
            'harga' => fake()->randomFloat(2, 1, 500),
            'id_user' => User::factory(),
        ];
    }
}
