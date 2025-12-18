<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseStock extends Model
{
    use HasFactory;

    protected $fillable = ['warehouse_id', 'product_id', 'stock'];

    protected $casts = [
        'stock' => 'integer',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public static function getAvailableStock($productId, $warehouseId)
    {
        return self::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->sum('stock');
    }
}
