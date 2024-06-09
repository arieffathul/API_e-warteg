<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    public $table = 'carts';

    public function makanan()
    {
        return $this->belongsTo(Makanan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'pembeli_id');
    }
}
