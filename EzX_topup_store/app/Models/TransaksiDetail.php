<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiDetail extends Model
{
    use HasFactory;

    protected $table = 'transaksi_detail';

    protected $primaryKey = 'id_transaksi_detail';

    protected $fillable = [
        'jenis_transaksi',
        'jumlah',
        'tanggal_transaksi',
        'harga',
        'id_transaksi',
        'id_package',
    ];

    protected $casts = [
        'tanggal_transaksi' => 'datetime',
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi', 'id_transaksi');
    }

    public function package()
    {
        return $this->belongsTo(GamePackage::class, 'id_package', 'id_package');
    }
}
