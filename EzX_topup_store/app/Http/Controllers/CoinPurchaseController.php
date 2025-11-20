<?php

namespace App\Http\Controllers;

use App\Models\CoinPurchase;
use App\Models\Transaksi;
use App\Notifications\CoinPurchaseStatusNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use Illuminate\View\View;

class CoinPurchaseController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $packages = Config::get('coin.packages', []);
        $paymentMethods = Config::get('coin.payment_methods', []);
        $rate = (int) Config::get('coin.coin_to_idr_rate', 100);

        $validated = $request->validateWithBag('coinPurchase', [
            'package_key' => ['required', 'string', 'in:' . implode(',', array_keys($packages))],
            'payment_method' => ['required', 'string', 'in:' . implode(',', array_keys($paymentMethods))],
        ]);

        $selectedPackage = $packages[$validated['package_key']];
        $paymentMethodConfig = $paymentMethods[$validated['payment_method']];

        $price = (int) $selectedPackage['price'];
        $coinAmount = (int) round($price / max($rate, 1));

        $transaksi = Transaksi::create([
            'jenis_transaksi' => 'purchase',
            'jumlah' => $coinAmount,
            'harga' => $price,
            'status' => 'pending',
            'id_user' => $user->id_user,
            'tanggal_transaksi' => now(),
        ]);

        $purchase = CoinPurchase::create([
            'id_user' => $user->id_user,
            'id_transaksi' => $transaksi->id_transaksi,
            'package_key' => $validated['package_key'],
            'coin_amount' => $coinAmount,
            'price_idr' => $price,
            'payment_method' => $validated['payment_method'],
            'payment_meta' => $this->buildPaymentMeta($paymentMethodConfig),
        ]);

        $user->notify(new CoinPurchaseStatusNotification($purchase, 'pending'));

        return redirect()->route('coins.purchases.show', $purchase)
            ->with('status', 'Permintaan top up koin berhasil dibuat. Selesaikan pembayaran dan tunggu persetujuan admin.');
    }

    public function show(Request $request, CoinPurchase $coinPurchase): View
    {
        $this->authorizePurchase($request, $coinPurchase);

        $paymentFromConfig = Config::get('coin.payment_methods.' . $coinPurchase->payment_method, []);
        $paymentConfig = array_merge($paymentFromConfig, $coinPurchase->payment_meta ?? []);

        return view('coins.confirm', [
            'purchase' => $coinPurchase->load('user'),
            'paymentConfig' => $paymentConfig,
        ]);
    }

    public function uploadPaymentProof(Request $request, CoinPurchase $coinPurchase): RedirectResponse
    {
        $this->authorizePurchase($request, $coinPurchase);

        if (in_array($coinPurchase->status, ['approved', 'rejected'], true)) {
            return redirect()->route('coins.purchases.show', $coinPurchase)
                ->with('status', 'Transaksi ini sudah diproses.');
        }

        $request->validate([
            'payment_proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ], [
            'payment_proof.required' => 'Bukti pembayaran wajib diunggah.',
            'payment_proof.image' => 'File bukti pembayaran harus berupa gambar.',
            'payment_proof.mimes' => 'Format bukti pembayaran harus JPG, JPEG, PNG, atau WEBP.',
            'payment_proof.max' => 'Ukuran bukti pembayaran maksimal 3MB.',
        ]);

        $path = $request->file('payment_proof')->store('payment-proofs/coin-purchases', 'public');

        if ($coinPurchase->payment_proof_path) {
            Storage::disk('public')->delete($coinPurchase->payment_proof_path);
        }

        $statusBefore = $coinPurchase->status;
        $paymentMeta = $coinPurchase->payment_meta ?? [];
        $paymentMeta['proof_uploaded_at'] = now()->toISOString();

        $coinPurchase->fill([
            'payment_proof_path' => $path,
            'payment_meta' => $paymentMeta,
            'status' => 'waiting_verification',
            'rejected_at' => null,
            'rejection_reason' => null,
        ])->save();

        if ($statusBefore !== 'waiting_verification') {
            $coinPurchase->user?->notify(new CoinPurchaseStatusNotification($coinPurchase->fresh(), 'waiting_verification'));
        }

        return redirect()->route('coins.purchases.show', $coinPurchase)
            ->with('status', 'Bukti pembayaran berhasil diunggah. Menunggu verifikasi admin.');
    }

    protected function authorizePurchase(Request $request, CoinPurchase $coinPurchase): void
    {
        if ($request->user()->cannot('view', $coinPurchase)) {
            abort(403);
        }
    }

    protected function buildPaymentMeta(array $config): array
    {
        return [
            'label' => Arr::get($config, 'label'),
            'type' => Arr::get($config, 'type'),
            'account_name' => Arr::get($config, 'account_name'),
            'account_number' => Arr::get($config, 'account_number'),
            'qr_image_url' => Arr::get($config, 'qr_image_url'),
            'instructions' => Arr::get($config, 'instructions', []),
        ];
    }

    public function previewPaymentProof(Request $request, CoinPurchase $coinPurchase)
    {
        $this->authorizePurchase($request, $coinPurchase);

        if (! $coinPurchase->payment_proof_path || ! Storage::disk('public')->exists($coinPurchase->payment_proof_path)) {
            abort(404);
        }

        return Storage::disk('public')->response($coinPurchase->payment_proof_path);
    }
}
