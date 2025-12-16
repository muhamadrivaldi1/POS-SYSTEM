<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_id',
        'product_id',
        'qty',
        'price',
        'subtotal'
    ];

    protected $casts = [
        'qty' => 'integer',
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function return()
    {
        return $this->belongsTo(ReturnModel::class, 'return_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
