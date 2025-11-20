<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CoinPurchase extends Model
{
    use HasFactory;

    protected $table = 'coin_purchases';

    protected $primaryKey = 'id_coin_purchase';

    protected $fillable = [
        'id_user',
        'id_transaksi',
        'transaction_code',
        'package_key',
        'coin_amount',
        'price_idr',
        'payment_method',
        'status',
        'payment_meta',
        'payment_proof_path',
        'approved_at',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'payment_meta' => 'array',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $purchase) {
            if (empty($purchase->transaction_code)) {
                $purchase->transaction_code = 'CP-' . Str::upper(Str::random(10));
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'id_coin_purchase';
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi', 'id_transaksi');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'waiting_verification']);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function getPaymentProofUrlAttribute(): ?string
    {
        if (! $this->payment_proof_path) {
            return null;
        }

        if (! Storage::disk('public')->exists($this->payment_proof_path)) {
            return null;
        }

        return route('coins.purchases.payment-proof.preview', $this);
    }
}
