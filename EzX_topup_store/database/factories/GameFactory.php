<?php

namespace Database\Factories;

use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Game>
 */
class GameFactory extends Factory
{
    protected $model = Game::class;

    public function definition(): array
    {
        return [
            'gambar' => fake()->imageUrl(600, 800, 'game', true),
            'nama_game' => fake()->unique()->words(3, true),
            'deskripsi' => fake()->paragraph(),
        ];
    }
}
