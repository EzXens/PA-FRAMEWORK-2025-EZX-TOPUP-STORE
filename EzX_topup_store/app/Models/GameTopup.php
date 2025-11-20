<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GameTopup extends Model
{
    use HasFactory;

    protected $table = 'game_topups';

    protected $primaryKey = 'id_game_topup';

    protected $fillable = [
        'transaction_code',
        'id_user',
        'id_game',
        'id_currency',
        'id_package',
        'id_transaksi',
        'price_idr',
        'price_before_discount',
        'discount_percentage',
        'premium_reward_coins',
        'payment_method',
        'status',
        'account_data',
        'contact_email',
        'contact_whatsapp',
        'payment_meta',
        'payment_proof_path',
        'approved_at',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'account_data' => 'array',
        'payment_meta' => 'array',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'discount_percentage' => 'integer',
        'premium_reward_coins' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $topup) {
            if (empty($topup->transaction_code)) {
                $topup->transaction_code = 'GT-' . Str::upper(Str::random(10));
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'id_game_topup';
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function game()
    {
        return $this->belongsTo(Game::class, 'id_game', 'id_game');
    }

    public function currency()
    {
        return $this->belongsTo(GameCurrency::class, 'id_currency', 'id_currency');
    }

    public function package()
    {
        return $this->belongsTo(GamePackage::class, 'id_package', 'id_package');
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

        return route('game-topups.payment-proof.preview', [
            'gameTopup' => $this,
            'transaction_code' => $this->transaction_code,
        ]);
    }
}
