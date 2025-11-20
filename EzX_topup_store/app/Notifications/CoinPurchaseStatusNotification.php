<?php

namespace App\Notifications;

use App\Models\CoinPurchase;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CoinPurchaseStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected CoinPurchase $purchase,
        protected string $status
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $status = $this->status;

        $titleMap = [
            'pending' => 'Transaksi sedang menunggu persetujuan',
            'waiting_verification' => 'Bukti pembayaran menunggu verifikasi',
            'approved' => 'Transaksi berhasil',
            'rejected' => 'Transaksi ditolak',
        ];

        $messageMap = [
            'pending' => 'Transaksi koin #' . $this->purchase->transaction_code . ' telah dibuat dan menunggu persetujuan admin.',
            'waiting_verification' => 'Bukti pembayaran untuk transaksi koin #' . $this->purchase->transaction_code . ' telah diterima dan menunggu verifikasi admin.',
            'approved' => 'Transaksi koin #' . $this->purchase->transaction_code . ' berhasil disetujui. Saldo koin Anda telah diperbarui.',
            'rejected' => 'Transaksi koin #' . $this->purchase->transaction_code . ' ditolak oleh admin. Silakan periksa detail pembayaran Anda.',
        ];

        $message = $messageMap[$status] ?? '';

        if ($status === 'rejected' && $this->purchase->rejection_reason) {
            $message .= ' Alasan: ' . $this->purchase->rejection_reason;
        }

        return [
            'transaction_code' => $this->purchase->transaction_code,
            'status' => $status,
            'title' => $titleMap[$status] ?? 'Notifikasi transaksi',
            'message' => $message,
            'coin_amount' => $this->purchase->coin_amount,
            'price_idr' => $this->purchase->price_idr,
            'payment_method' => $this->purchase->payment_method,
            'rejection_reason' => $this->purchase->rejection_reason,
        ];
    }
}
