<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    public $table = 'transactions';

    public $guarded = [];

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }
}
