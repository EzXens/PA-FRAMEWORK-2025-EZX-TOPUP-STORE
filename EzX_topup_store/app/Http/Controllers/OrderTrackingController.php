<?php

namespace App\Http\Controllers;

use App\Models\GameTopup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class OrderTrackingController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $transactionCode = trim((string) $request->input('transaction_code', ''));
        $topup = null;

        if ($request->isMethod('post')) {
            $validator = Validator::make($request->all(), [
                'transaction_code' => ['required', 'string', 'max:255'],
            ], [
                'transaction_code.required' => 'Masukkan ID transaksi Anda.',
            ]);

            if ($validator->fails()) {
                return back()
                    ->withErrors($validator, 'orderTrack')
                    ->withInput();
            }

            $transactionCode = trim((string) $validator->validated()['transaction_code']);

            $exists = GameTopup::where('transaction_code', $transactionCode)->exists();

            if (! $exists) {
                return back()
                    ->withErrors([
                        'transaction_code' => 'Pesanan dengan ID tersebut tidak ditemukan.',
                    ], 'orderTrack')
                    ->withInput();
            }

            return redirect()
                ->route('orders.track', ['transaction_code' => $transactionCode])
                ->with('status', 'Pesanan berhasil ditemukan.');
        }

        if ($transactionCode !== '') {
            $topup = GameTopup::with(['game', 'currency', 'package'])
                ->where('transaction_code', $transactionCode)
                ->first();
        }

        return view('orders.track', [
            'transactionCode' => $transactionCode,
            'topup' => $topup,
        ]);
    }

    public function confirm(string $transactionCode): View
    {
        $topup = GameTopup::with(['game', 'currency', 'package'])
            ->where('transaction_code', $transactionCode)
            ->firstOrFail();

        $paymentConfig = array_merge(
            Config::get('coin.payment_methods.' . $topup->payment_method, []),
            $topup->payment_meta ?? []
        );

        return view('game-topups.confirm', [
            'topup' => $topup,
            'paymentConfig' => $paymentConfig,
        ]);
    }
}
