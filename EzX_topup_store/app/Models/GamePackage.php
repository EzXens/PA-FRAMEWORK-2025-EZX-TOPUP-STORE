<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GamePackage extends Model
{
    use HasFactory;

    protected $table = 'game_packages';

    protected $primaryKey = 'id_package';

    protected $fillable = [
        'amount',
        'price',
        'deskripsi',
        'id_currency',
    ];

    public function currency()
    {
        return $this->belongsTo(GameCurrency::class, 'id_currency', 'id_currency');
    }

    public function transactionDetails()
    {
        return $this->hasMany(TransaksiDetail::class, 'id_package', 'id_package');
    }
}
