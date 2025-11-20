<?php

namespace Database\Factories;

use App\Models\GameCurrency;
use App\Models\GamePackage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GamePackage>
 */
class GamePackageFactory extends Factory
{
    protected $model = GamePackage::class;

    public function definition(): array
    {
        return [
            'amount' => fake()->numberBetween(50, 5000),
            'price' => fake()->randomFloat(2, 0.99, 199.99),
            'deskripsi' => fake()->sentence(),
            'id_currency' => GameCurrency::factory(),
        ];
    }
}
