<?php

namespace App\Notifications;

use App\Models\GameTopup;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GameTopupStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected GameTopup $topup,
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
            'pending' => 'Pesanan top up sedang diproses',
            'waiting_verification' => 'Bukti pembayaran menunggu verifikasi',
            'approved' => 'Pesanan top up berhasil',
            'rejected' => 'Pesanan top up ditolak',
        ];

        $messageMap = [
            'pending' => 'Pesanan top up #' . $this->topup->transaction_code . ' telah dibuat dan menunggu persetujuan admin.',
            'waiting_verification' => 'Bukti pembayaran untuk pesanan top up #' . $this->topup->transaction_code . ' telah diterima dan menunggu verifikasi admin.',
            'approved' => 'Pesanan top up #' . $this->topup->transaction_code . ' telah disetujui. Item akan segera diproses.',
            'rejected' => 'Pesanan top up #' . $this->topup->transaction_code . ' ditolak oleh admin. Silakan cek ulang data akun dan bukti pembayaran.',
        ];

        $message = $messageMap[$status] ?? '';

        if ($status === 'rejected' && $this->topup->rejection_reason) {
            $message .= ' Alasan: ' . $this->topup->rejection_reason;
        }

        return [
            'transaction_code' => $this->topup->transaction_code,
            'status' => $status,
            'title' => $titleMap[$status] ?? 'Notifikasi top up',
            'message' => $message,
            'game' => $this->topup->game?->nama_game,
            'package' => $this->topup->package?->amount,
            'price_idr' => $this->topup->price_idr,
            'payment_method' => $this->topup->payment_method,
            'coins_used' => $this->topup->payment_meta['coins_used'] ?? null,
            'rejection_reason' => $this->topup->rejection_reason,
        ];
    }
}
