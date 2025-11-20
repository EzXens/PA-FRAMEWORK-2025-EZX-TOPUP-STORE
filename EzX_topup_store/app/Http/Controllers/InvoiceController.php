<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function show(Request $request, Transaksi $transaksi): View
    {
        $transaksi->load([
            'user',
            'gameTopup.game',
            'gameTopup.currency',
            'gameTopup.package',
            'coinPurchase',
        ]);

        $this->authorizeView($request, $transaksi);

        $invoiceNumber = $this->resolveInvoiceNumber($transaksi);
        $billingName = $this->resolveBillingName($transaksi);
        $billingEmail = $this->resolveBillingEmail($transaksi);
        $billingPhone = $this->resolveBillingPhone($transaksi);
        $items = $this->buildInvoiceItems($transaksi);
        $paymentMethod = $this->resolvePaymentMethodLabel($transaksi);

        return view('invoices.show', [
            'transaksi' => $transaksi,
            'invoiceNumber' => $invoiceNumber,
            'billingName' => $billingName,
            'billingEmail' => $billingEmail,
            'billingPhone' => $billingPhone,
            'items' => $items,
            'paymentMethod' => $paymentMethod,
        ]);
    }

    protected function authorizeView(Request $request, Transaksi $transaksi): void
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        if (in_array($user->role, ['admin', 'super_admin'], true)) {
            return;
        }

        if ($transaksi->id_user && $user->id_user === $transaksi->id_user) {
            return;
        }

        abort(403);
    }

    protected function resolveInvoiceNumber(Transaksi $transaksi): string
    {
        return $transaksi->gameTopup?->transaction_code
            ?? $transaksi->coinPurchase?->transaction_code
            ?? 'TRX-' . str_pad((string) $transaksi->id_transaksi, 6, '0', STR_PAD_LEFT);
    }

    protected function resolveBillingName(Transaksi $transaksi): string
    {
        if ($transaksi->user) {
            return $transaksi->user->nama_lengkap
                ?? $transaksi->user->username
                ?? 'Customer';
        }

        $topup = $transaksi->gameTopup;
        if ($topup && $topup->contact_whatsapp) {
            return 'Guest (' . $topup->contact_whatsapp . ')';
        }

        return 'Guest Customer';
    }

    protected function resolveBillingEmail(Transaksi $transaksi): ?string
    {
        if ($transaksi->user) {
            return $transaksi->user->email;
        }

        return $transaksi->gameTopup?->contact_email;
    }

    protected function resolveBillingPhone(Transaksi $transaksi): ?string
    {
        if ($transaksi->user && $transaksi->user->nomor_telepon) {
            return $transaksi->user->nomor_telepon;
        }

        return $transaksi->gameTopup?->contact_whatsapp;
    }

    protected function buildInvoiceItems(Transaksi $transaksi): array
    {
        $items = [];

        if ($topup = $transaksi->gameTopup) {
            $gameName = $topup->game?->nama_game ?? 'Game';
            $currencyName = $topup->currency?->currency_name ?? '';
            $amount = $topup->package?->amount ?? $transaksi->jumlah;
            $detail = trim($gameName . ' • ' . number_format($amount) . ' ' . $currencyName);

            $accountParts = collect($topup->account_data ?? [])
                ->map(fn ($value, $label) => strtoupper(str_replace('_', ' ', $label)) . ': ' . $value)
                ->implode(' | ');

            if ($accountParts) {
                $detail .= ' • ' . $accountParts;
            }

            $items[] = [
                'description' => $detail,
                'quantity' => 1,
                'unit_price' => (float) $transaksi->harga,
                'total' => (float) $transaksi->harga,
            ];
        } elseif ($coinPurchase = $transaksi->coinPurchase) {
            $packages = Config::get('coin.packages', []);
            $packageLabel = Arr::get($packages, $coinPurchase->package_key . '.label');
            $description = $packageLabel
                ? $packageLabel . ' • ' . number_format($coinPurchase->coin_amount) . ' koin'
                : number_format($coinPurchase->coin_amount) . ' koin';

            $items[] = [
                'description' => $description,
                'quantity' => 1,
                'unit_price' => (float) $transaksi->harga,
                'total' => (float) $transaksi->harga,
            ];
        } else {
            $items[] = [
                'description' => ucfirst($transaksi->jenis_transaksi ?? 'Transaksi'),
                'quantity' => max(1, (int) $transaksi->jumlah),
                'unit_price' => (float) $transaksi->harga,
                'total' => (float) $transaksi->harga * max(1, (int) $transaksi->jumlah),
            ];
        }

        return $items;
    }

    protected function resolvePaymentMethodLabel(Transaksi $transaksi): string
    {
        if ($topup = $transaksi->gameTopup) {
            $config = Config::get('coin.payment_methods.' . $topup->payment_method, []);
            return $config['label'] ?? strtoupper($topup->payment_method ?? '');
        }

        if ($coinPurchase = $transaksi->coinPurchase) {
            $config = Config::get('coin.payment_methods.' . $coinPurchase->payment_method, []);
            return $config['label'] ?? strtoupper($coinPurchase->payment_method ?? '');
        }

        return strtoupper($transaksi->jenis_transaksi ?? '');
    }
}
