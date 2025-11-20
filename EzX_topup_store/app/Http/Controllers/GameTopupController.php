<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameCurrency;
use App\Models\GamePackage;
use App\Models\GameTopup;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use App\Notifications\GameTopupStatusNotification;
use App\Services\PremiumService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class GameTopupController extends Controller
{
    public function store(Request $request, Game $game): RedirectResponse
    {
        $user = $request->user();

        $paymentMethods = Config::get('coin.payment_methods', []);
        $availablePaymentMethods = $paymentMethods;

        if (! $user) {
            $availablePaymentMethods = array_filter(
                $paymentMethods,
                static fn ($config, $key) => ($config['type'] ?? '') !== 'coin' && $key !== 'coins',
                ARRAY_FILTER_USE_BOTH
            );
        }

        $paymentMethodKeys = array_keys($availablePaymentMethods);

        $validator = Validator::make($request->all(), [
            'currency' => ['required', 'integer', 'exists:game_currencies,id_currency'],
            'package' => ['required', 'integer', 'exists:game_packages,id_package'],
            'payment_method' => ['required', 'string', 'in:' . implode(',', $paymentMethodKeys)],
            'email' => ['nullable', 'email'],
            'whatsapp' => ['nullable', 'string', 'max:25'],
            'account' => ['required', 'array'],
        ], [
            'currency.required' => 'Pilih produk terlebih dahulu.',
            'package.required' => 'Pilih paket yang ingin dibeli.',
            'payment_method.required' => 'Pilih metode pembayaran.',
        ]);

        $validator->after(function ($validator) use ($request, $game, $availablePaymentMethods) {
            $currencyId = (int) $request->input('currency');
            $packageId = (int) $request->input('package');

            $currency = GameCurrency::where('id_currency', $currencyId)
                ->where('id_game', $game->id_game)
                ->first();

            if (! $currency) {
                $validator->errors()->add('currency', 'Produk tidak ditemukan untuk game ini.');
                return;
            }

            $package = GamePackage::where('id_package', $packageId)
                ->where('id_currency', $currency->id_currency)
                ->first();

            if (! $package) {
                $validator->errors()->add('package', 'Paket tidak valid untuk produk yang dipilih.');
            }

            $paymentMethod = $request->input('payment_method');
            if (! array_key_exists($paymentMethod, $availablePaymentMethods)) {
                $validator->errors()->add('payment_method', 'Metode pembayaran tidak tersedia.');
            }

            if ($paymentMethod === 'coins' || (($availablePaymentMethods[$paymentMethod]['type'] ?? null) === 'coin')) {
                if (! $request->user()) {
                    $validator->errors()->add('payment_method', 'Masuk untuk menggunakan saldo koin.');
                }
            }

            $accountDefinition = Config::get('game_account_fields');
            $accountKey = Str::slug($game->nama_game);
            $fields = Arr::get($accountDefinition, $accountKey . '.fields', Arr::get($accountDefinition, 'default.fields', []));

            foreach ($fields as $field) {
                $name = $field['name'];
                $value = Arr::get($request->input('account'), $name);
                if (! $value || (is_string($value) && trim($value) === '')) {
                    $validator->errors()->add("account.$name", ($field['label'] ?? 'Field') . ' wajib diisi.');
                }
            }
        });

        $validator->validate();

        $currency = GameCurrency::with('game')->findOrFail($request->input('currency'));
        $package = GamePackage::findOrFail($request->input('package'));
        $selectedPaymentMethod = $request->input('payment_method');
        $paymentMethodConfig = $paymentMethods[$selectedPaymentMethod] ?? [];
        $accountData = array_filter(
            array_map(static fn ($value) => is_string($value) ? trim($value) : $value, $request->input('account', [])),
            static fn ($value) => filled($value)
        );

        $coinRate = max((int) Config::get('coin.coin_to_idr_rate', 100), 1);
        $payingWithCoins = $selectedPaymentMethod === 'coins';
        $coinsNeeded = null;
        $premiumService = app(PremiumService::class);
        $originalPrice = (int) round($package->price);
        $discountPercentage = $user ? $premiumService->calculateDiscountPercentage($user) : 0;
        $finalPrice = $originalPrice;

        if ($user && $premiumService->isPremiumActive($user) && $discountPercentage > 0) {
            $discountAmount = (int) round($originalPrice * ($discountPercentage / 100));
            $finalPrice = max(0, $originalPrice - $discountAmount);
        }

        if ($payingWithCoins && ! $user) {
            throw ValidationException::withMessages([
                'payment_method' => 'Masuk untuk menggunakan saldo koin.',
            ]);
        }

        if ($payingWithCoins) {
            $priceIdr = (float) $finalPrice;
            $coinsNeeded = (int) max(1, ceil($priceIdr / $coinRate));
            $walletBalance = optional($user->koin)->jumlah_koin ?? 0;

            if ($walletBalance < $coinsNeeded) {
                return redirect()->back()->withErrors([
                    'payment_method' => 'Saldo koin Anda tidak mencukupi. Dibutuhkan ' . number_format($coinsNeeded) . ' koin.',
                ])->withInput();
            }
        }

        $topup = DB::transaction(function () use ($user, $game, $currency, $package, $paymentMethodConfig, $request, $accountData, $payingWithCoins, $coinsNeeded, $coinRate, $selectedPaymentMethod, $finalPrice, $originalPrice, $discountPercentage) {
            $paymentMeta = $this->buildPaymentMeta($paymentMethodConfig);
            $initialStatus = 'pending';

            if ($payingWithCoins) {
                $wallet = $user->koin()->lockForUpdate()->first();

                if (! $wallet || $wallet->jumlah_koin < $coinsNeeded) {
                    throw ValidationException::withMessages([
                        'payment_method' => 'Saldo koin Anda tidak mencukupi. Silakan pilih metode lain.',
                    ]);
                }

                $paymentMeta = array_merge($paymentMeta, [
                    'coins_used' => $coinsNeeded,
                    'coin_rate' => $coinRate,
                ]);

                $initialStatus = 'waiting_verification';
            }

            if ($discountPercentage > 0) {
                $paymentMeta = array_merge($paymentMeta, [
                    'premium_discount_percentage' => $discountPercentage,
                    'price_before_discount' => $originalPrice,
                ]);
            }

            $transaksi = Transaksi::create([
                'jenis_transaksi' => 'topup',
                'jumlah' => $package->amount,
                'harga' => $finalPrice,
                'status' => 'pending',
                'id_user' => $user?->id_user,
                'tanggal_transaksi' => now(),
            ]);

            $topup = GameTopup::create([
                'id_user' => $user?->id_user,
                'id_game' => $game->id_game,
                'id_currency' => $currency->id_currency,
                'id_package' => $package->id_package,
                'id_transaksi' => $transaksi->id_transaksi,
                'price_idr' => $finalPrice,
                'price_before_discount' => $originalPrice,
                'discount_percentage' => $discountPercentage,
                'payment_method' => $selectedPaymentMethod,
                'status' => $initialStatus,
                'account_data' => $accountData,
                'contact_email' => $request->input('email'),
                'contact_whatsapp' => $request->input('whatsapp'),
                'payment_meta' => $paymentMeta,
            ]);

            TransaksiDetail::create([
                'jenis_transaksi' => 'topup',
                'jumlah' => $package->amount,
                'tanggal_transaksi' => now(),
                'harga' => $finalPrice,
                'id_transaksi' => $transaksi->id_transaksi,
                'id_package' => $package->id_package,
            ]);

            return $topup;
        });

        if ($user) {
            $statusForNotification = $payingWithCoins ? 'waiting_verification' : 'pending';
            $user->notify(new GameTopupStatusNotification($topup, $statusForNotification));

            return redirect()->route('game-topups.show', $topup)
                ->with('status', 'Pesanan top up berhasil dibuat. Selesaikan pembayaran dan tunggu persetujuan admin.');
        }

        return redirect()->route('orders.confirm', ['transaction_code' => $topup->transaction_code])
            ->with('status', 'Pesanan top up berhasil dibuat. Simpan ID transaksi Anda untuk konfirmasi pembayaran.');
    }

    public function show(Request $request, GameTopup $gameTopup): View
    {
        $this->authorize('view', $gameTopup);

        $paymentConfig = array_merge(
            Config::get('coin.payment_methods.' . $gameTopup->payment_method, []),
            $gameTopup->payment_meta ?? []
        );

        return view('game-topups.confirm', [
            'topup' => $gameTopup->load(['game', 'package', 'currency']),
            'paymentConfig' => $paymentConfig,
        ]);
    }

    public function uploadPaymentProof(Request $request, GameTopup $gameTopup): RedirectResponse
    {
        $user = $request->user();

        if ($user) {
            $this->authorize('view', $gameTopup);
        } else {
            if ($gameTopup->id_user) {
                return redirect()
                    ->route('orders.confirm', ['transaction_code' => $gameTopup->transaction_code])
                    ->with('status', 'Masuk untuk melanjutkan unggah bukti pembayaran.');
            }

            $transactionCode = (string) $request->input('transaction_code');

            if (! hash_equals($gameTopup->transaction_code, $transactionCode)) {
                return redirect()
                    ->route('orders.confirm', ['transaction_code' => $gameTopup->transaction_code])
                    ->with('status', 'Kode transaksi tidak valid.');
            }
        }

        if (in_array($gameTopup->status, ['approved', 'rejected'], true)) {
            return $this->redirectAfterUpload($gameTopup, $user)
                ->with('status', 'Transaksi ini sudah diproses.');
        }

        $paymentType = $gameTopup->payment_meta['type'] ?? null;
        if ($gameTopup->payment_method === 'coins' || $paymentType === 'coin') {
            return $this->redirectAfterUpload($gameTopup, $user)
                ->with('status', 'Transaksi menggunakan koin tidak memerlukan bukti pembayaran.');
        }

        $request->validate([
            'payment_proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'transaction_code' => ['nullable', 'string'],
        ], [
            'payment_proof.required' => 'Bukti pembayaran wajib diunggah.',
            'payment_proof.image' => 'File bukti pembayaran harus berupa gambar.',
            'payment_proof.mimes' => 'Format bukti pembayaran harus JPG, JPEG, PNG, atau WEBP.',
            'payment_proof.max' => 'Ukuran bukti pembayaran maksimal 3MB.',
        ]);

        $file = $request->file('payment_proof');

        $path = $file->store('payment-proofs/game-topups', 'public');

        if ($gameTopup->payment_proof_path) {
            Storage::disk('public')->delete($gameTopup->payment_proof_path);
        }

        $statusBefore = $gameTopup->status;

        $paymentMeta = $gameTopup->payment_meta ?? [];
        $paymentMeta['proof_uploaded_at'] = now()->toISOString();

        $gameTopup->fill([
            'payment_proof_path' => $path,
            'payment_meta' => $paymentMeta,
            'status' => 'waiting_verification',
            'rejected_at' => null,
            'rejection_reason' => null,
        ])->save();

        if ($statusBefore !== 'waiting_verification') {
            $gameTopup->user?->notify(new GameTopupStatusNotification($gameTopup->fresh(), 'waiting_verification'));
        }

        return $this->redirectAfterUpload($gameTopup, $user)
            ->with('status', 'Bukti pembayaran berhasil diunggah. Menunggu verifikasi admin.');
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

    public function previewPaymentProof(Request $request, GameTopup $gameTopup)
    {
        $user = $request->user();

        if ($user) {
            $this->authorize('view', $gameTopup);
        } else {
            if ($gameTopup->id_user) {
                abort(403);
            }

            $transactionCode = (string) $request->query('transaction_code');
            if (! hash_equals($gameTopup->transaction_code, $transactionCode)) {
                abort(403);
            }
        }

        if (! $gameTopup->payment_proof_path || ! Storage::disk('public')->exists($gameTopup->payment_proof_path)) {
            abort(404);
        }

        return Storage::disk('public')->response($gameTopup->payment_proof_path);
    }

    protected function redirectAfterUpload(GameTopup $gameTopup, ?\App\Models\User $user = null): RedirectResponse
    {
        if ($user) {
            return redirect()->route('game-topups.show', $gameTopup);
        }

        return redirect()->route('orders.confirm', ['transaction_code' => $gameTopup->transaction_code]);
    }
}
