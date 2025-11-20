<?php

return [
    'default' => [
        'title' => 'Detail Akun',
        'description' => 'Lengkapi data akun yang dibutuhkan agar tim kami dapat memproses top-up dengan tepat.',
        'fields' => [
            [
                'name' => 'player_id',
                'label' => 'Player ID',
                'type' => 'text',
                'placeholder' => 'Masukkan Player ID',
                'helper' => 'Pastikan ID sesuai dengan yang tertera di dalam game.',
            ],
        ],
    ],
    'mobile-legends' => [
        'title' => 'Detail Akun Mobile Legends',
        'description' => 'Masukkan ID dan Server Anda. Contoh format: 123456789 (1234).',
        'fields' => [
            [
                'name' => 'player_id',
                'label' => 'ID',
                'type' => 'number',
                'placeholder' => 'Contoh: 123456789',
                'helper' => 'Lihat pada profil game Anda bagian ID.',
            ],
            [
                'name' => 'server_id',
                'label' => 'Server',
                'type' => 'number',
                'placeholder' => 'Contoh: 1234',
                'helper' => 'Dapat dilihat di profil setelah ID dalam tanda kurung.',
            ],
        ],
    ],
    'genshin-impact' => [
        'title' => 'Detail Akun Genshin Impact',
        'description' => 'Masukkan UID dan pilih server tempat akun Anda berada.',
        'fields' => [
            [
                'name' => 'uid',
                'label' => 'UID',
                'type' => 'number',
                'placeholder' => 'Contoh: 800123456',
                'helper' => 'UID dapat dilihat di pojok kanan bawah layar utama.',
            ],
            [
                'name' => 'server',
                'label' => 'Server',
                'type' => 'select',
                'options' => ['Asia', 'America', 'Europe', 'TW/HK/MO'],
                'placeholder' => 'Pilih server akun',
                'helper' => 'Gunakan server sesuai akun agar top-up masuk.',
            ],
        ],
    ],
    'pubg-mobile' => [
        'title' => 'Detail Akun PUBG Mobile',
        'description' => 'Masukkan ID karakter dan pilih platform login.',
        'fields' => [
            [
                'name' => 'character_id',
                'label' => 'Character ID',
                'type' => 'number',
                'placeholder' => 'Contoh: 5243567898',
                'helper' => 'Ditemukan pada profil PUBG Mobile Anda.',
            ],
            [
                'name' => 'platform',
                'label' => 'Platform',
                'type' => 'select',
                'options' => ['Android', 'iOS', 'Emulator'],
                'placeholder' => 'Pilih platform login',
                'helper' => 'Pilih platform yang digunakan untuk login ke game.',
            ],
        ],
    ],
    'free-fire' => [
        'title' => 'Detail Akun Free Fire',
        'description' => 'Masukkan ID pemain atau login menggunakan UID sosial.',
        'fields' => [
            [
                'name' => 'player_id',
                'label' => 'Player ID',
                'type' => 'number',
                'placeholder' => 'Contoh: 1234567890',
                'helper' => 'Dapat dilihat di bagian profil pemain.',
            ],
            [
                'name' => 'login_option',
                'label' => 'Metode Login',
                'type' => 'select',
                'options' => ['Facebook', 'Google', 'VK', 'Twitter'],
                'placeholder' => 'Pilih metode login',
                'helper' => 'Pilih metode login yang terhubung dengan akun.',
            ],
        ],
    ],
];
