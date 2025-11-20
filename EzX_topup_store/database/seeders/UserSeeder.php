<?php

namespace Database\Seeders;

use App\Models\Koin;
use App\Models\Premium;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::factory()->create([
            'email' => 'superadmin@example.com',
            'username' => 'superadmin',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
        ]);

        Koin::factory()->for($superAdmin, 'user')->create([
            'jumlah_koin' => 250000,
        ]);

        Premium::factory()->for($superAdmin, 'user')->state(fn () => [
            'status' => 'active',
        ])->create();

        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        Koin::factory()->for($admin, 'user')->create([
            'jumlah_koin' => 100000,
        ]);

        Premium::factory()->for($admin, 'user')->state(fn () => [
            'status' => 'active',
        ])->create();

        User::factory(8)->create()->each(function (User $user) {
            Koin::factory()->for($user, 'user')->create();
            Premium::factory()->for($user, 'user')->create();
        });
    }
}
