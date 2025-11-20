<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\CoinPurchase;
use App\Models\GameTopup;
use App\Models\Koin;
use App\Models\Premium;
use App\Models\Transaksi;
use App\Services\PremiumService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'id_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'username',
        'nama_lengkap',
        'nomor_telepon',
        'tanggal_lahir',
        'bio',
        'password',
        'role',
        'foto_profil',
        'background_profil',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'tanggal_lahir' => 'date',
            'password' => 'hashed',
        ];
    }

    public function koin()
    {
        return $this->hasOne(Koin::class, 'id_user', 'id_user');
    }

    public function coinPurchases()
    {
        return $this->hasMany(CoinPurchase::class, 'id_user', 'id_user');
    }

    public function premium()
    {
        return $this->hasOne(Premium::class, 'id_user', 'id_user');
    }

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'id_user', 'id_user');
    }

    public function gameTopups()
    {
        return $this->hasMany(GameTopup::class, 'id_user', 'id_user');
    }

    public function getCoinBalanceAttribute(): int
    {
        return optional($this->koin)->jumlah_koin ?? 0;
    }

    public function getIsPremiumAttribute(): bool
    {
        return app(PremiumService::class)->isPremiumActive($this);
    }

    public function getPremiumDiscountAttribute(): int
    {
        return app(PremiumService::class)->calculateDiscountPercentage($this);
    }
}
