<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPrice extends Model
{
    protected $table = 'product_prices';

    protected $fillable = [
        'product_id',
        'price_tier_id',
        'price',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function priceTier()
    {
        return $this->belongsTo(PriceTier::class);
    }
}
