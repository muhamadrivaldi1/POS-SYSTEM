<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBarcode extends Model
{
    protected $table = 'product_barcodes';

    protected $fillable = [
        'product_id',
        'barcode',
        'packaging_type',
        'qty_per_package',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
