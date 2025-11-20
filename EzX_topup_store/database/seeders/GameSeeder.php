<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\GameCurrency;
use App\Models\GamePackage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        $games = [
            [
                'nama_game' => 'Mobile Legends',
                'deskripsi' => 'Game MOBA populer dengan pertarungan 5v5 real-time.',
                'currencies' => [
                    [
                        'name' => 'Diamonds',
                        'deskripsi' => 'Mata uang premium untuk membeli hero, skin, dan item eksklusif.',
                        'packages' => [
                            ['amount' => 86, 'price' => 20000, 'deskripsi' => 'Diamonds 86 + bonus 8'],
                            ['amount' => 172, 'price' => 40000, 'deskripsi' => 'Diamonds 172 + bonus 16'],
                            ['amount' => 257, 'price' => 60000, 'deskripsi' => 'Diamonds 257 + bonus 23'],
                            ['amount' => 514, 'price' => 110000, 'deskripsi' => 'Diamonds 514 + bonus 46'],
                        ],
                    ],
                    [
                        'name' => 'Twilight Pass',
                        'deskripsi' => 'Langganan bulanan untuk bonus harian dan misi eksklusif.',
                        'packages' => [
                            ['amount' => 1, 'price' => 149000, 'deskripsi' => 'Twilight Pass 30 Hari'],
                        ],
                    ],
                ],
            ],
            [
                'nama_game' => 'PUBG Mobile',
                'deskripsi' => 'Battle royale realistis dengan 100 pemain dalam satu match.',
                'currencies' => [
                    [
                        'name' => 'Unknown Cash (UC)',
                        'deskripsi' => 'Digunakan untuk membeli Royal Pass, skin senjata, dan item premium.',
                        'packages' => [
                            ['amount' => 60, 'price' => 15000, 'deskripsi' => 'UC 60 + bonus 3'],
                            ['amount' => 325, 'price' => 70000, 'deskripsi' => 'UC 325 + bonus 25'],
                            ['amount' => 660, 'price' => 135000, 'deskripsi' => 'UC 660 + bonus 55'],
                            ['amount' => 1800, 'price' => 360000, 'deskripsi' => 'UC 1800 + bonus 180'],
                        ],
                    ],
                ],
            ],
            [
                'nama_game' => 'Roblox',
                'deskripsi' => 'Platform game sandbox untuk membuat dan memainkan dunia virtual.',
                'currencies' => [
                    [
                        'name' => 'Robux',
                        'deskripsi' => 'Mata uang untuk membeli avatar, game pass, dan item eksklusif.',
                        'packages' => [
                            ['amount' => 80, 'price' => 15000, 'deskripsi' => 'Robux 80'],
                            ['amount' => 400, 'price' => 70000, 'deskripsi' => 'Robux 400'],
                            ['amount' => 800, 'price' => 135000, 'deskripsi' => 'Robux 800'],
                            ['amount' => 1700, 'price' => 275000, 'deskripsi' => 'Robux 1700'],
                        ],
                    ],
                ],
            ],
            [
                'nama_game' => 'Genshin Impact',
                'deskripsi' => 'Action RPG dunia terbuka dengan sistem gacha karakter.',
                'currencies' => [
                    [
                        'name' => 'Genesis Crystal',
                        'deskripsi' => 'Mata uang premium yang bisa ditukar menjadi Primogem.',
                        'packages' => [
                            ['amount' => 60, 'price' => 15000, 'deskripsi' => 'Genesis Crystal 60'],
                            ['amount' => 300, 'price' => 75000, 'deskripsi' => 'Genesis Crystal 300'],
                            ['amount' => 980, 'price' => 225000, 'deskripsi' => 'Genesis Crystal 980'],
                            ['amount' => 1980, 'price' => 425000, 'deskripsi' => 'Genesis Crystal 1980'],
                        ],
                    ],
                    [
                        'name' => 'Blessing of the Welkin Moon',
                        'deskripsi' => 'Langganan 30 hari dengan Primogem harian dan bonus Genesis Crystal.',
                        'packages' => [
                            ['amount' => 1, 'price' => 75000, 'deskripsi' => 'Blessing of the Welkin Moon'],
                        ],
                    ],
                ],
            ],
            [
                'nama_game' => 'Wuthering Waves',
                'deskripsi' => 'ARPG futuristik dengan sistem resonator dan eksplorasi dunia luas.',
                'currencies' => [
                    [
                        'name' => 'Astrite',
                        'deskripsi' => 'Mata uang premium untuk gacha dan pembelian item khusus.',
                        'packages' => [
                            ['amount' => 60, 'price' => 14000, 'deskripsi' => 'Astrite 60'],
                            ['amount' => 330, 'price' => 70000, 'deskripsi' => 'Astrite 330'],
                            ['amount' => 1090, 'price' => 220000, 'deskripsi' => 'Astrite 1090'],
                            ['amount' => 2240, 'price' => 430000, 'deskripsi' => 'Astrite 2240'],
                        ],
                    ],
                ],
            ],
            [
                'nama_game' => 'Free Fire',
                'deskripsi' => 'Battle royale cepat dengan ronde singkat dan gameplay intens.',
                'currencies' => [
                    [
                        'name' => 'Diamonds',
                        'deskripsi' => 'Digunakan untuk membeli bundle, karakter, dan skin senjata.',
                        'packages' => [
                            ['amount' => 70, 'price' => 12000, 'deskripsi' => 'Diamonds 70'],
                            ['amount' => 140, 'price' => 24000, 'deskripsi' => 'Diamonds 140'],
                            ['amount' => 355, 'price' => 60000, 'deskripsi' => 'Diamonds 355'],
                            ['amount' => 720, 'price' => 120000, 'deskripsi' => 'Diamonds 720'],
                        ],
                    ],
                ],
            ],
        ];

        DB::transaction(function () use ($games) {
            foreach ($games as $gameData) {
                $game = Game::updateOrCreate(
                    ['nama_game' => $gameData['nama_game']],
                    [
                        'deskripsi' => $gameData['deskripsi'],
                        'gambar' => $gameData['gambar'] ?? null,
                    ]
                );

                foreach ($gameData['currencies'] as $currencyData) {
                    $currency = GameCurrency::updateOrCreate(
                        [
                            'id_game' => $game->id_game,
                            'currency_name' => $currencyData['name'],
                        ],
                        [
                            'deskripsi' => $currencyData['deskripsi'],
                            'gambar_currency' => $currencyData['gambar_currency'] ?? null,
                        ]
                    );

                    foreach ($currencyData['packages'] as $packageData) {
                        GamePackage::updateOrCreate(
                            [
                                'id_currency' => $currency->id_currency,
                                'amount' => $packageData['amount'],
                            ],
                            [
                                'price' => $packageData['price'],
                                'deskripsi' => $packageData['deskripsi'],
                            ]
                        );
                    }
                }
            }
        });
    }
}
