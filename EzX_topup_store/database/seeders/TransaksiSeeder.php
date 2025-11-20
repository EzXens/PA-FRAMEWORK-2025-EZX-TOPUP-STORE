<?php

namespace Database\Seeders;

use App\Models\GamePackage;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransaksiSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $packages = GamePackage::all();

        if ($users->isEmpty() || $packages->isEmpty()) {
            return;
        }

        Transaksi::factory(20)->make()->each(function (Transaksi $transaksi) use ($users, $packages) {
            $user = $users->random();
            $transaksi->id_user = $user->id_user;
            $transaksi->save();

            $detailCount = fake()->numberBetween(1, 3);
            $totalHarga = 0;
            $totalJumlah = 0;

            for ($i = 0; $i < $detailCount; $i++) {
                $package = $packages->random();
                $jumlah = fake()->numberBetween(1, 5);
                $harga = $package->price * $jumlah;

                TransaksiDetail::create([
                    'jenis_transaksi' => $transaksi->jenis_transaksi,
                    'jumlah' => $jumlah,
                    'tanggal_transaksi' => $transaksi->tanggal_transaksi,
                    'harga' => $harga,
                    'id_transaksi' => $transaksi->id_transaksi,
                    'id_package' => $package->id_package,
                ]);

                $totalHarga += $harga;
                $totalJumlah += $jumlah;
            }

            $transaksi->update([
                'jumlah' => $totalJumlah,
                'harga' => $totalHarga,
            ]);
        });
    }
}
