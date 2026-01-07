<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Category;
use App\Models\WarehouseStock;
use App\Models\ProductBarcode;
use App\Models\ProductPrice;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category_id',
        'description',
        'purchase_price',
        'base_price',
        'stock',
        'min_stock',
        'unit',
        'image',
        'status'
    ];

    /* ================= RELATIONS ================= */

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function warehouseStocks()
    {
        return $this->hasMany(WarehouseStock::class);
    }

    // ✅ PAKAI ProductBarcode
    public function barcodes(): HasMany
    {
        return $this->hasMany(ProductBarcode::class);
    }

    // ✅ PAKAI ProductPrice
    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }
}
