<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Game extends Model
{
    use HasFactory;

    protected $table = 'games';

    protected $primaryKey = 'id_game';

    protected $fillable = [
        'gambar',
        'nama_game',
        'deskripsi',
    ];

    protected $appends = ['gambar_url'];

    public function getRouteKeyName()
    {
        return 'id_game';
    }

    public function currencies()
    {
        return $this->hasMany(GameCurrency::class, 'id_game', 'id_game');
    }

    public function getGambarUrlAttribute()
    {
        return $this->gambar 
            ? asset('storage/' . $this->gambar)
            : null;
    }
}
