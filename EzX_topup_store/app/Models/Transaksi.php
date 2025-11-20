<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksi';

    protected $primaryKey = 'id_transaksi';

    protected $fillable = [
        'jenis_transaksi',
        'jumlah',
        'tanggal_transaksi',
        'harga',
        'status',
        'id_user',
    ];

    protected $casts = [
        'tanggal_transaksi' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function details()
    {
        return $this->hasMany(TransaksiDetail::class, 'id_transaksi', 'id_transaksi');
    }

    public function coinPurchase()
    {
        return $this->hasOne(CoinPurchase::class, 'id_transaksi', 'id_transaksi');
    }

    public function gameTopup()
    {
        return $this->hasOne(GameTopup::class, 'id_transaksi', 'id_transaksi');
    }
}
