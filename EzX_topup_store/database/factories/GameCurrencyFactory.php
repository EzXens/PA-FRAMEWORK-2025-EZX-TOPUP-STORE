<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\GameCurrency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GameCurrency>
 */
class GameCurrencyFactory extends Factory
{
    protected $model = GameCurrency::class;

    public function definition(): array
    {
        return [
            'currency_name' => fake()->unique()->word(),
            'gambar_currency' => fake()->imageUrl(400, 400, 'currency', true),
            'deskripsi' => fake()->sentence(),
            'id_game' => Game::factory(),
        ];
    }
}
