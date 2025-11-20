<?php

namespace Database\Factories;

use App\Models\Premium;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Premium>
 */
class PremiumFactory extends Factory
{
    protected $model = Premium::class;

    public function definition(): array
    {
        $status = fake()->randomElement(['active', 'inactive']);
        $start = $status === 'active' ? Carbon::now()->subDays(fake()->numberBetween(1, 30)) : null;
        $end = $start?->copy()->addDays(fake()->numberBetween(30, 120));

        return [
            'status' => $status,
            'tanggal_berlangganan' => $start,
            'tanggal_expired' => $status === 'active' ? $end : null,
            'id_user' => User::factory(),
        ];
    }
}
