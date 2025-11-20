<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Koin extends Model
{
    use HasFactory;

    protected $table = 'koin';

    protected $primaryKey = 'id_koin';

    protected $fillable = [
        'id_user',
        'jumlah_koin',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
