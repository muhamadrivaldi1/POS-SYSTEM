<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    // =========================
    // RELATIONS
    // =========================

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function barcodes()
    {
        return $this->hasMany(ProductBarcode::class);
    }

    public function prices()
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function warehouseStocks()
    {
        return $this->hasMany(WarehouseStock::class);
    }

    // =========================
    // GET STOCK SESUAI GUDANG
    // =========================
    public function getStockByWarehouse(?int $warehouseId = null): int
    {
        // Ambil stok dari warehouse_stocks jika warehouse_id diberikan
        if ($warehouseId) {
            $stock = $this->warehouseStocks()
                ->where('warehouse_id', $warehouseId)
                ->value('stock');

            return $stock !== null ? (int) $stock : 0;
        }

        // Jika tidak pakai gudang, kembalikan stok global
        return (int) $this->stock;
    }
}
