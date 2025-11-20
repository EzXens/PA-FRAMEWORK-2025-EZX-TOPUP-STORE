<?php

namespace App\Policies;

use App\Models\CoinPurchase;
use App\Models\User;

class CoinPurchasePolicy
{
    public function view(User $user, CoinPurchase $coinPurchase): bool
    {
        if ($user->role === 'super_admin' || $user->role === 'admin') {
            return true;
        }

        return $coinPurchase->id_user === $user->id_user;
    }

    public function approve(User $user, CoinPurchase $coinPurchase): bool
    {
        return $user->role === 'admin' || $user->role === 'super_admin';
    }
}
