<?php

namespace App\Http\Controllers;

use App\Models\GameTopup;
use App\Notifications\GameTopupStatusNotification;
use App\Services\PremiumService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminGameTopupApprovalController extends Controller
{
    public function approve(Request $request, GameTopup $gameTopup): RedirectResponse
    {
        $this->authorize('approve', $gameTopup);

        if (! in_array($gameTopup->status, ['pending', 'waiting_verification'], true)) {
            return redirect()->route('admin.dashboard')
                ->with('status', 'Transaksi sudah diproses sebelumnya.')
                ->with('admin_active_tab', 'approval');
        }

        $premiumService = app(PremiumService::class);

        try {
            DB::transaction(function () use ($gameTopup, $premiumService) {
                $paymentMeta = $gameTopup->payment_meta ?? [];

                $requiresCoinCharge = $gameTopup->payment_method === 'coins'
                    || (($paymentMeta['type'] ?? null) === 'coin');

                if ($requiresCoinCharge) {
                    $coinsUsed = (int) ($paymentMeta['coins_used'] ?? 0);

                    if ($coinsUsed > 0) {
                        $user = $gameTopup->user;

                        if (! $user) {
                            throw ValidationException::withMessages([
                                'payment_method' => 'Pengguna tidak ditemukan untuk memotong saldo koin.',
                            ]);
                        }

                        $wallet = $user->koin()->lockForUpdate()->first();

                        if (! $wallet || $wallet->jumlah_koin < $coinsUsed) {
                            throw ValidationException::withMessages([
                                'payment_method' => 'Saldo koin pengguna tidak mencukupi untuk disetujui.',
                            ]);
                        }

                        $wallet->decrement('jumlah_koin', $coinsUsed);
                        $paymentMeta['coins_charged_at'] = now()->toISOString();
                    }
                }

                $rewardCoins = $premiumService->applyRewardsForApprovedTopup($gameTopup);

                if ($rewardCoins > 0) {
                    $paymentMeta['premium_reward_coins'] = $rewardCoins;
                }

                $gameTopup->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                    'payment_meta' => $paymentMeta,
                    'rejected_at' => null,
                    'rejection_reason' => null,
                    'premium_reward_coins' => $rewardCoins > 0 ? $rewardCoins : $gameTopup->premium_reward_coins,
                ]);

                if ($gameTopup->transaksi) {
                    $gameTopup->transaksi->update(['status' => 'completed']);
                }

                $gameTopup->user?->notify(new GameTopupStatusNotification($gameTopup, 'approved'));
            });
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first()
                ?? 'Gagal menyetujui transaksi.';

            return redirect()->route('admin.dashboard')
                ->with('status', $message)
                ->with('admin_active_tab', 'approval');
        }

        return redirect()->route('admin.dashboard')
            ->with('status', 'Pesanan top up disetujui.')
            ->with('admin_active_tab', 'approval');
    }

    public function reject(Request $request, GameTopup $gameTopup): RedirectResponse
    {
        $this->authorize('approve', $gameTopup);

        if (! in_array($gameTopup->status, ['pending', 'waiting_verification'], true)) {
            return redirect()->route('admin.dashboard')
                ->with('status', 'Transaksi sudah diproses sebelumnya.')
                ->with('admin_active_tab', 'approval');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $gameTopup->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        if ($gameTopup->transaksi) {
            $gameTopup->transaksi->update(['status' => 'failed']);
        }

        $gameTopup->user?->notify(new GameTopupStatusNotification($gameTopup, 'rejected'));

        return redirect()->route('admin.dashboard')
            ->with('status', 'Pesanan top up ditolak.')
            ->with('admin_active_tab', 'approval');
    }
}
