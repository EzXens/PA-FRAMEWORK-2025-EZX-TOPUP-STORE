<?php

namespace App\Providers;

use App\Models\CoinPurchase;
use App\Models\GameTopup;
use App\Policies\CoinPurchasePolicy;
use App\Policies\GameTopupPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     */
    protected $policies = [
        CoinPurchase::class => CoinPurchasePolicy::class,
        GameTopup::class => GameTopupPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
