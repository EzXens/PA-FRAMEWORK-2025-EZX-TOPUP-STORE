<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class SuperAdminDashboardController extends Controller
{
    public function index(): View
    {
        $totalUsers = User::where('role', 'user')->count();
        $totalAdmins = User::where('role', 'admin')->count();
        $totalTransactions = Transaksi::count();
        $totalRevenue = Transaksi::where('status', 'completed')->sum('harga');

        $periodStartRaw = Transaksi::min('tanggal_transaksi');
        $periodEndRaw = Transaksi::max('tanggal_transaksi');
        $periodStart = $periodStartRaw ? Carbon::parse($periodStartRaw) : null;
        $periodEnd = $periodEndRaw ? Carbon::parse($periodEndRaw) : null;

        $periodLabel = match (true) {
            $periodStart && $periodEnd => $periodStart->timezone(config('app.timezone'))->format('d M Y') . ' - ' . $periodEnd->timezone(config('app.timezone'))->format('d M Y'),
            default => 'Belum ada transaksi',
        };

        $pendingAmount = Transaksi::where('status', 'pending')->sum('harga');
        $monthlyFinancials = Transaksi::selectRaw('DATE_FORMAT(tanggal_transaksi, "%Y-%m") as month_key')
            ->selectRaw('SUM(CASE WHEN status = "completed" THEN harga ELSE 0 END) as revenue')
            ->selectRaw('SUM(CASE WHEN status = "pending" THEN harga ELSE 0 END) as pending_amount')
            ->selectRaw('COUNT(*) as transaction_count')
            ->groupBy('month_key')
            ->orderByDesc('month_key')
            ->limit(12)
            ->get()
            ->map(function ($row) {
                $period = Carbon::createFromFormat('Y-m', $row->month_key)->locale(config('app.locale', 'id'))->translatedFormat('F Y');

                return [
                    'month_key' => $row->month_key,
                    'label' => $period,
                    'revenue' => (int) $row->revenue,
                    'pending' => (int) $row->pending_amount,
                    'transaction_count' => (int) $row->transaction_count,
                ];
            });

        $averageMonthlyRevenue = $monthlyFinancials->count() > 0
            ? (int) round($monthlyFinancials->avg('revenue'))
            : 0;

        $monthlyChartSource = $monthlyFinancials->sortBy('month_key')->values();
        $monthlyChartData = [
            'labels' => $monthlyChartSource->pluck('label'),
            'revenue' => $monthlyChartSource->pluck('revenue'),
            'pending' => $monthlyChartSource->pluck('pending'),
        ];

        $transactionTypeChartData = Transaksi::select('jenis_transaksi')
            ->selectRaw('COUNT(*) as total_transactions')
            ->selectRaw('SUM(CASE WHEN status = "completed" THEN harga ELSE 0 END) as completed_revenue')
            ->groupBy('jenis_transaksi')
            ->orderByDesc('total_transactions')
            ->get()
            ->map(fn ($row) => [
                'type' => $row->jenis_transaksi ?? 'lainnya',
                'total' => (int) $row->total_transactions,
                'revenue' => (int) $row->completed_revenue,
            ]);

        $financialNotes = [];

        if ($pendingAmount > 0) {
            $financialNotes[] = 'Transaksi pending saat ini senilai Rp ' . number_format($pendingAmount, 0, ',', '.') . '. Dorong tim admin untuk memproses pembayaran secepatnya.';
        }

        if ($monthlyFinancials->isNotEmpty()) {
            $latest = $monthlyFinancials->first();
            $financialNotes[] = 'Pendapatan bulan ' . $latest['label'] . ' tercatat sebesar Rp ' . number_format($latest['revenue'], 0, ',', '.') . ' dengan potensi tambahan Rp ' . number_format($latest['pending'], 0, ',', '.') . ' dari transaksi pending.';
        }

        if (empty($financialNotes)) {
            $financialNotes[] = 'Belum ada catatan khusus. Pertahankan performa transaksi saat ini.';
        }

        $users = User::where('role', 'user')
            ->orderByDesc('created_at')
            ->get();

        $admins = User::where('role', 'admin')
            ->orderByDesc('created_at')
            ->get();

        return view('superadmin.dashboard.index', [
            'stats' => [
                'totalUsers' => $totalUsers,
                'totalAdmins' => $totalAdmins,
                'totalTransactions' => $totalTransactions,
                'totalRevenue' => $totalRevenue,
            ],
            'users' => $users,
            'admins' => $admins,
            'financialOverview' => [
                'periodLabel' => $periodLabel,
                'grossRevenue' => (int) $totalRevenue,
                'pendingAmount' => (int) $pendingAmount,
                'averageMonthlyRevenue' => $averageMonthlyRevenue,
            ],
            'financialNotes' => $financialNotes,
            'monthlyFinancials' => $monthlyFinancials,
            'monthlyChartData' => $monthlyChartData,
            'transactionTypeChartData' => $transactionTypeChartData,
        ]);
    }
}
