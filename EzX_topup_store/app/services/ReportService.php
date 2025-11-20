<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Mengambil laporan keuangan gabungan dari game_topups + coin_purchases
     */
    public function getMonthlyFinancials()
    {
        // Query Game Topup
        $gameTopup = DB::table('game_topups')
            ->selectRaw("
                DATE_FORMAT(created_at, '%Y-%m') AS bulan,
                SUM(CASE WHEN status = 'approved' THEN price_idr ELSE 0 END) AS revenue,
                SUM(CASE WHEN status = 'pending' THEN price_idr ELSE 0 END) AS pending,
                COUNT(*) AS transaction_count
            ")
            ->groupBy('bulan');

        // Query Coin Purchase
        $coinPurchase = DB::table('coin_purchases')
            ->selectRaw("
                DATE_FORMAT(created_at, '%Y-%m') AS bulan,
                SUM(CASE WHEN status = 'approved' THEN price_idr ELSE 0 END) AS revenue,
                SUM(CASE WHEN status = 'pending' THEN price_idr ELSE 0 END) AS pending,
                COUNT(*) AS transaction_count
            ")
            ->groupBy('bulan');

        // UNION data dari 2 tabel
        $combined = DB::query()
            ->fromSub($gameTopup->unionAll($coinPurchase), 'report')
            ->selectRaw("
                bulan,
                SUM(revenue) AS revenue,
                SUM(pending) AS pending,
                SUM(transaction_count) AS transaction_count
            ")
            ->groupBy('bulan')
            ->orderBy('bulan', 'asc')
            ->get();

        return $combined->transform(function ($item) {
            return [
                'label' => $item->bulan,
                'revenue' => (int) $item->revenue,
                'pending' => (int) $item->pending,
                'transaction_count' => (int) $item->transaction_count,
            ];
        });
    }
}
