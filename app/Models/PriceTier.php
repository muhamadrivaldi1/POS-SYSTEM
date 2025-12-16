<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceTier extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'priority', 'status'];

    public function productPrices()
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
