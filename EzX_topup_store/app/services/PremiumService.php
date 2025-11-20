<?php

namespace App\Services;

use App\Models\GameTopup;
use App\Models\Premium;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PremiumService
{
    public function isPremiumActive(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $premium = $user->premium;

        return $this->isPremiumInstanceActive($premium);
    }

    public function calculateDiscountPercentage(?User $user): int
    {
        if (! $user) {
            return 0;
        }

        $premium = $user->premium;

        if (! $this->isPremiumInstanceActive($premium)) {
            return 0;
        }

        $base = (int) Config::get('premium.base_discount', 5);
        $max = (int) Config::get('premium.max_discount', $base);
        $increment = max((int) Config::get('premium.discount_increment', 1), 0);
        $purchases = max((int) ($premium->total_successful_topups ?? 0), 0);

        if ($increment === 0) {
            return min($max, $base);
        }

        $additional = min($purchases * $increment, max($max - $base, 0));

        return min($max, $base + $additional);
    }

    public function getPremiumPriceIdr(): int
    {
        return (int) Config::get('premium.price_idr', 0);
    }

    public function getPremiumPriceCoins(): int
    {
        $coinRate = max((int) Config::get('coin.coin_to_idr_rate', 100), 1);

        return (int) max(1, (int) ceil($this->getPremiumPriceIdr() / $coinRate));
    }

    /**
     * @return array{premium: Premium, transaction: Transaksi, coins_used: int}
     *
     * @throws ValidationException
     */
    public function purchase(User $user): array
    {
        $priceCoins = $this->getPremiumPriceCoins();
        $priceIdr = $this->getPremiumPriceIdr();
        $durationDays = max((int) Config::get('premium.duration_days', 30), 1);

        return DB::transaction(function () use ($user, $priceCoins, $priceIdr, $durationDays) {
            $wallet = $user->koin()->lockForUpdate()->firstOrCreate([], [
                'jumlah_koin' => 0,
            ]);

            if (! $wallet || $wallet->jumlah_koin < $priceCoins) {
                throw ValidationException::withMessages([
                    'balance' => 'Saldo koin Anda tidak mencukupi untuk membeli premium.',
                ]);
            }

            $wallet->decrement('jumlah_koin', $priceCoins);

            $premium = $user->premium()->lockForUpdate()->first();
            $now = now();
            $startsAt = $now;
            $expiredAt = $now->clone()->addDays($durationDays);

            if ($premium && $this->isPremiumInstanceActive($premium)) {
                $startsAt = $premium->tanggal_berlangganan ?? $now;
                $expiredAt = ($premium->tanggal_expired ?? $now)->clone()->addDays($durationDays);
            }

            if (! $premium) {
                $premium = new Premium([
                    'id_user' => $user->id_user,
                ]);
            }

            $premium->fill([
                'status' => 'active',
                'tanggal_berlangganan' => $startsAt,
                'tanggal_expired' => $expiredAt,
            ]);

            if ($premium->total_successful_topups === null) {
                $premium->total_successful_topups = 0;
            }

            $premium->save();

            $transaction = Transaksi::create([
                'jenis_transaksi' => 'premium',
                'jumlah' => 1,
                'harga' => $priceIdr,
                'status' => 'completed',
                'id_user' => $user->id_user,
                'tanggal_transaksi' => now(),
            ]);

            return [
                'premium' => $premium,
                'transaction' => $transaction,
                'coins_used' => $priceCoins,
            ];
        });
    }

    public function applyRewardsForApprovedTopup(GameTopup $gameTopup): int
    {
        $user = $gameTopup->user;

        if (! $user) {
            return 0;
        }

        if ((int) $gameTopup->premium_reward_coins > 0) {
            return 0;
        }

        return DB::transaction(function () use ($user, $gameTopup) {
            $premium = $user->premium()->lockForUpdate()->first();

            if (! $this->isPremiumInstanceActive($premium)) {
                return 0;
            }

            $rewardCoins = max((int) Config::get('premium.reward_coins_per_purchase', 0), 0);

            $premium->increment('total_successful_topups');

            if ($rewardCoins <= 0) {
                return 0;
            }

            $wallet = $user->koin()->lockForUpdate()->firstOrCreate([], [
                'jumlah_koin' => 0,
            ]);

            $wallet->increment('jumlah_koin', $rewardCoins);

            return $rewardCoins;
        });
    }

    protected function isPremiumInstanceActive(?Premium $premium): bool
    {
        if (! $premium) {
            return false;
        }

        if (strtolower((string) $premium->status) !== 'active') {
            return false;
        }

        $expiredAt = $premium->tanggal_expired;

        return $expiredAt === null || $expiredAt->isFuture();
    }
}
