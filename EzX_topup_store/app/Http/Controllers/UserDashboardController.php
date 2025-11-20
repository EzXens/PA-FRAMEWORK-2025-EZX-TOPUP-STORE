<?php

namespace App\Http\Controllers;

use App\Models\GameTopup;
use App\Models\Transaksi;
use App\Services\PremiumService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->loadMissing(['koin', 'premium']);
        $premiumService = app(PremiumService::class);

        $coinPurchases = $user->coinPurchases()
            ->with(['transaksi'])
            ->latest()
            ->take(10)
            ->get();

        $gameTopups = $user->gameTopups()
            ->with(['game', 'currency', 'package', 'transaksi'])
            ->latest()
            ->take(10)
            ->get();

        $now = now();
        $monthlyTransactions = $user->transaksi()
            ->whereBetween('tanggal_transaksi', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->count();

        $monthlySpending = $user->transaksi()
            ->whereBetween('tanggal_transaksi', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->sum('harga');

        $pendingCoinPurchaseCount = $user->coinPurchases()->pending()->count();
        $pendingGameTopupCount = $user->gameTopups()->pending()->count();

        $premiumTransactions = Transaksi::query()
            ->where('jenis_transaksi', 'premium')
            ->where('id_user', $user->id_user)
            ->latest('tanggal_transaksi')
            ->take(10)
            ->get();

        $favoriteGames = GameTopup::selectRaw('games.nama_game as name, COUNT(*) as total_orders, SUM(game_topups.price_idr) as total_spent')
            ->join('games', 'games.id_game', '=', 'game_topups.id_game')
            ->where('game_topups.id_user', $user->id_user)
            ->whereIn('game_topups.status', ['approved', 'completed'])
            ->groupBy('game_topups.id_game', 'games.nama_game')
            ->orderByDesc('total_orders')
            ->limit(3)
            ->get();

        $coinSummary = [
            'total_coin' => $user->coinPurchases()->whereIn('status', ['approved', 'completed'])->sum('coin_amount'),
            'total_spent' => $user->coinPurchases()->whereIn('status', ['approved', 'completed'])->sum('price_idr'),
            'successful_orders' => $user->coinPurchases()->whereIn('status', ['approved', 'completed'])->count(),
            'last_purchase_at' => optional($user->coinPurchases()->latest('created_at')->first())->created_at,
        ];

        $lastGameTopup = $user->gameTopups()
            ->with(['game', 'currency', 'package'])
            ->latest('created_at')
            ->first();

        $premiumActive = $premiumService->isPremiumActive($user);
        $currentDiscount = $premiumService->calculateDiscountPercentage($user);
        $priceCoins = $premiumService->getPremiumPriceCoins();
        $priceIdr = $premiumService->getPremiumPriceIdr();
        $baseDiscount = (int) config('premium.base_discount', 5);
        $maxDiscount = (int) config('premium.max_discount', $baseDiscount);
        $increment = max((int) config('premium.discount_increment', 1), 0);
        $purchasesCount = max(optional($user->premium)->total_successful_topups ?? 0, 0);
        $nextDiscount = $currentDiscount;

        if ($premiumActive && $increment > 0 && $currentDiscount < $maxDiscount) {
            $nextDiscount = min($maxDiscount, $baseDiscount + (($purchasesCount + 1) * $increment));
        }

        return view('user.dashboard.index', [
            'user' => $user,
            'coinPurchases' => $coinPurchases,
            'gameTopups' => $gameTopups,
            'monthlyTransactions' => $monthlyTransactions,
            'monthlySpending' => $monthlySpending,
            'pendingCoinPurchaseCount' => $pendingCoinPurchaseCount,
            'pendingGameTopupCount' => $pendingGameTopupCount,
            'premiumTransactions' => $premiumTransactions,
            'premiumActive' => $premiumActive,
            'premiumDiscount' => $currentDiscount,
            'premiumPriceCoins' => $priceCoins,
            'premiumPriceIdr' => $priceIdr,
            'premiumNextDiscount' => $nextDiscount,
            'premiumMaxDiscount' => $maxDiscount,
            'premiumBaseDiscount' => $baseDiscount,
            'premiumRewardCoins' => (int) config('premium.reward_coins_per_purchase', 20),
            'premiumIncrement' => $increment,
            'favoriteGames' => $favoriteGames,
            'coinSummary' => $coinSummary,
            'lastGameTopup' => $lastGameTopup,
        ]);
    }
}
