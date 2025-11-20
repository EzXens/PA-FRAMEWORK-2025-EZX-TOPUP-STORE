<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class GameCurrency extends Model
{
    use HasFactory;

    protected $table = 'game_currencies';

    protected $primaryKey = 'id_currency';

    protected $fillable = [
        'currency_name',
        'gambar_currency',
        'deskripsi',
        'id_game',
    ];

    protected $appends = ['gambar_currency_url'];

    public function game()
    {
        return $this->belongsTo(Game::class, 'id_game', 'id_game');
    }

    public function packages()
    {
        return $this->hasMany(GamePackage::class, 'id_currency', 'id_currency');
    }

     public function getGambarCurrencyUrlAttribute()
    {
        return $this->gambar_currency
            ? asset('storage/' . $this->gambar_currency)
            : null;
    }
}
