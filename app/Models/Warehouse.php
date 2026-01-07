<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'warehouses';

    protected $fillable = [
        'name',
        'address',
        'phone',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /* =====================
     | RELATIONS
     ===================== */

    public function warehouseStocks()
    {
        return $this->hasMany(WarehouseStock::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    /* =====================
     | HELPERS
     ===================== */

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}