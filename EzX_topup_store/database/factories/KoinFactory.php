<?php

namespace Database\Factories;

use App\Models\Koin;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Koin>
 */
class KoinFactory extends Factory
{
    protected $model = Koin::class;

    public function definition(): array
    {
        return [
            'jumlah_koin' => fake()->numberBetween(0, 100000),
            'id_user' => User::factory(),
        ];
    }
}
