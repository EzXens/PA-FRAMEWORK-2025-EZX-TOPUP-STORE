<?php

namespace App\Policies;

use App\Models\GameTopup;
use App\Models\User;

class GameTopupPolicy
{
    public function view(User $user, GameTopup $gameTopup): bool
    {
        if (in_array($user->role, ['admin', 'super_admin'])) {
            return true;
        }

        return $gameTopup->id_user === $user->id_user;
    }

    public function approve(User $user, GameTopup $gameTopup): bool
    {
        return in_array($user->role, ['admin', 'super_admin']);
    }
}
