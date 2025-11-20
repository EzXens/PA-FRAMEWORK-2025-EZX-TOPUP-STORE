<?php

namespace App\Http\Controllers;

use App\Models\CoinPurchase;
use App\Models\Koin;
use App\Notifications\CoinPurchaseStatusNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminCoinPurchaseApprovalController extends Controller
{
    public function approve(Request $request, CoinPurchase $coinPurchase): RedirectResponse
    {
        $this->authorize('approve', $coinPurchase);

        if (! in_array($coinPurchase->status, ['pending', 'waiting_verification'], true)) {
            return redirect()->route('admin.dashboard')
                ->with('status', 'Transaksi sudah diproses sebelumnya.')
                ->with('admin_active_tab', 'approval');
        }

        DB::transaction(function () use ($coinPurchase) {
            $coinPurchase->update([
                'status' => 'approved',
                'approved_at' => now(),
                'rejected_at' => null,
                'rejection_reason' => null,
            ]);

            if ($coinPurchase->transaksi) {
                $coinPurchase->transaksi->update(['status' => 'completed']);
            }

            $wallet = Koin::firstOrCreate(
                ['id_user' => $coinPurchase->id_user],
                ['jumlah_koin' => 0]
            );

            $wallet->increment('jumlah_koin', $coinPurchase->coin_amount);

            $coinPurchase->user?->notify(new CoinPurchaseStatusNotification($coinPurchase, 'approved'));
        });

        return redirect()->route('admin.dashboard')
            ->with('status', 'Transaksi berhasil disetujui dan koin telah ditambahkan.')
            ->with('admin_active_tab', 'approval');
    }

    public function reject(Request $request, CoinPurchase $coinPurchase): RedirectResponse
    {
        $this->authorize('approve', $coinPurchase);

        if (! in_array($coinPurchase->status, ['pending', 'waiting_verification'], true)) {
            return redirect()->route('admin.dashboard')
                ->with('status', 'Transaksi sudah diproses sebelumnya.')
                ->with('admin_active_tab', 'approval');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $coinPurchase->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        if ($coinPurchase->transaksi) {
            $coinPurchase->transaksi->update(['status' => 'failed']);
        }

        $coinPurchase->user?->notify(new CoinPurchaseStatusNotification($coinPurchase, 'rejected'));

        return redirect()->route('admin.dashboard')
            ->with('status', 'Transaksi berhasil ditolak.')
            ->with('admin_active_tab', 'approval');
    }
}
