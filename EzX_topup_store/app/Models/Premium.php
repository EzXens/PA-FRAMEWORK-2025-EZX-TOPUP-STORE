<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Premium extends Model
{
    use HasFactory;

    protected $table = 'premiums';

    protected $primaryKey = 'id_premium';

    protected $fillable = [
        'status',
        'tanggal_berlangganan',
        'tanggal_expired',
        'id_user',
        'total_successful_topups',
    ];

    protected $casts = [
        'tanggal_berlangganan' => 'datetime',
        'tanggal_expired' => 'datetime',
        'total_successful_topups' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function isActive(): bool
    {
        if (strtolower((string) $this->status) !== 'active') {
            return false;
        }

        return $this->tanggal_expired === null || $this->tanggal_expired->isFuture();
    }
}
