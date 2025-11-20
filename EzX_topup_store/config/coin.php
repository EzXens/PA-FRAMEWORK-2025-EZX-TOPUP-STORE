<?php

return [
    'coin_to_idr_rate' => 100,

    'packages' => [
        '10k' => [
            'label' => 'Rp 10.000',
            'price' => 10_000,
        ],
        '25k' => [
            'label' => 'Rp 25.000',
            'price' => 25_000,
        ],
        '50k' => [
            'label' => 'Rp 50.000',
            'price' => 50_000,
        ],
        '100k' => [
            'label' => 'Rp 100.000',
            'price' => 100_000,
        ],
        '250k' => [
            'label' => 'Rp 250.000',
            'price' => 250_000,
        ],
        '500k' => [
            'label' => 'Rp 500.000',
            'price' => 500_000,
        ],
        '750k' => [
            'label' => 'Rp 750.000',
            'price' => 750_000,
        ],
        '1m' => [
            'label' => 'Rp 1.000.000',
            'price' => 1_000_000,
        ],
    ],

    'payment_methods' => [
        'coins' => [
            'label' => 'Saldo Koin Website',
            'type' => 'coin',
        ],
        'qris' => [
            'label' => 'QRIS',
            'type' => 'qris',
            'qr_image_url' => 'images/qris_ezxstore.png',
            'instructions' => [
                'Buka aplikasi bank atau e-wallet yang mendukung QRIS.',
                'Scan kode QR yang tersedia dan pastikan nominal sesuai.',
                'Konfirmasi pembayaran dan simpan bukti transaksi.',
            ],
        ],
        'bca' => [
            'label' => 'Transfer Bank BCA',
            'type' => 'bank_transfer',
            'account_name' => 'PT EzX Digital',
            'account_number' => '1234567890',
            'instructions' => [
                'Masuk ke aplikasi m-BCA atau ATM terdekat.',
                'Pilih menu Transfer ke Rekening BCA.',
                'Masukkan nomor rekening dan nominal sesuai pesanan.',
                'Selesaikan transfer dan simpan bukti pembayaran.',
            ],
        ],
        'mandiri' => [
            'label' => 'Transfer Bank Mandiri',
            'type' => 'bank_transfer',
            'account_name' => 'PT EzX Digital',
            'account_number' => '9876543210',
            'instructions' => [
                'Buka Livin by Mandiri atau kunjungi ATM Mandiri.',
                'Pilih transfer ke rekening Mandiri dan masukkan nomor tujuan.',
                'Input nominal sesuai pesanan dan konfirmasi pembayaran.',
            ],
        ],
        'bni' => [
            'label' => 'Transfer Bank BNI',
            'type' => 'bank_transfer',
            'account_name' => 'PT EzX Digital',
            'account_number' => '0099887766',
            'instructions' => [
                'Masuk ke BNI Mobile Banking atau ATM BNI.',
                'Pilih transfer antar rekening BNI dan masukkan nomor tujuan.',
                'Masukkan nominal sesuai tagihan dan konfirmasi pembayaran.',
            ],
        ],
        'dana' => [
            'label' => 'DANA',
            'type' => 'ewallet',
            'account_name' => 'EzX Store',
            'account_number' => '0812-0000-1234',
            'instructions' => [
                'Buka aplikasi DANA dan pilih menu Kirim.',
                'Masukkan nomor tujuan DANA dan nominal pembayaran.',
                'Periksa detail, lalu selesaikan pembayaran.',
            ],
        ],
        'ovo' => [
            'label' => 'OVO',
            'type' => 'ewallet',
            'account_name' => 'EzX Store',
            'account_number' => '0813-1111-4321',
            'instructions' => [
                'Buka aplikasi OVO dan pilih menu Transfer.',
                'Masukkan nomor tujuan OVO dan nominal pembayaran.',
                'Periksa detail transaksi dan konfirmasi.',
            ],
        ],
        'shopeepay' => [
            'label' => 'ShopeePay',
            'type' => 'ewallet',
            'account_name' => 'EzX Store',
            'account_number' => '0814-2222-7890',
            'instructions' => [
                'Buka aplikasi Shopee, pilih menu ShopeePay.',
                'Pilih transfer ke teman dan masukkan nomor ShopeePay tujuan.',
                'Masukkan nominal dan selesaikan pembayaran.',
            ],
        ],
    ],
];
