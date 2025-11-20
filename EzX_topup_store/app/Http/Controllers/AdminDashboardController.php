<?php

namespace App\Http\Controllers;

use App\Models\CoinPurchase;
use App\Models\Game;
use App\Models\GameCurrency;
use App\Models\GameTopup;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(Request $request): View
    {
        if ($request->filled('tab')) {
            session()->flash('admin_active_tab', $request->query('tab'));
        }

        $totalGames = Game::count();
        $totalRevenue = Transaksi::where('status', 'completed')->sum('harga');
        $completedTopUps = Transaksi::where('status', 'completed')->count();
        $activeUsers = User::where('role', 'user')->count();

        $games = Game::with(['currencies.packages'])->orderByDesc('created_at')->get();
        $topGames = Game::withCount('currencies')->orderByDesc('currencies_count')->take(5)->get();
        $topPurchasedGames = GameTopup::selectRaw('games.nama_game as name, COUNT(*) as total_orders, SUM(game_topups.price_idr) as total_revenue')
            ->join('games', 'games.id_game', '=', 'game_topups.id_game')
            ->whereIn('game_topups.status', ['approved', 'completed'])
            ->groupBy('game_topups.id_game', 'games.nama_game')
            ->orderByDesc('total_orders')
            ->limit(5)
            ->get();
        $topSpenders = Transaksi::selectRaw('id_user, SUM(harga) as total_spent, COUNT(*) as total_orders')
            ->whereNotNull('id_user')
            ->where('status', 'completed')
            ->with('user:id_user,username,email')
            ->groupBy('id_user')
            ->orderByDesc('total_spent')
            ->limit(5)
            ->get();
        $recentCurrencies = GameCurrency::with('game')->orderByDesc('created_at')->take(6)->get();
        $pendingCoinPurchases = CoinPurchase::with(['user'])
            ->pending()
            ->orderByDesc('created_at')
            ->take(10)
            ->get();
        $pendingGameTopups = GameTopup::with(['user', 'game', 'package', 'currency'])
            ->pending()
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        $orderType = $request->query('order_type', 'all');
        $allowedOrderTypes = ['topup', 'purchase'];
        if ($orderType !== 'all' && ! in_array($orderType, $allowedOrderTypes, true)) {
            $orderType = 'all';
        }

        $ordersQuery = Transaksi::with([
            'user',
            'gameTopup.game',
            'gameTopup.currency',
            'gameTopup.package',
            'coinPurchase',
        ])
            ->orderByDesc('tanggal_transaksi')
            ->orderByDesc('id_transaksi');

        if ($orderType !== 'all') {
            $ordersQuery->where('jenis_transaksi', $orderType);
        }

        $orders = $ordersQuery->paginate(10)->withQueryString();

        $coinPackages = Config::get('coin.packages', []);
        $orderTypeOptions = [
            'all' => 'Semua Jenis Transaksi',
            'topup' => 'Top Up Game',
            'purchase' => 'Top Up Koin',
        ];

        return view('admin.dashboard.index', [
            'stats' => [
                'totalGames' => $totalGames,
                'totalRevenue' => $totalRevenue,
                'completedTopUps' => $completedTopUps,
                'activeUsers' => $activeUsers,
            ],
            'games' => $games,
            'topGames' => $topGames,
            'topPurchasedGames' => $topPurchasedGames,
            'topSpenders' => $topSpenders,
            'recentCurrencies' => $recentCurrencies,
            'pendingCoinPurchases' => $pendingCoinPurchases,
            'pendingGameTopups' => $pendingGameTopups,
            'orders' => $orders,
            'orderType' => $orderType,
            'orderTypeOptions' => $orderTypeOptions,
            'coinPackages' => $coinPackages,
        ]);
    }
}
