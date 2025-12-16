<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'purchase_number',
        'user_id',
        'warehouse_id',
        'supplier_name',
        'purchase_date',
        'total',
        'status',
        'notes'
    ];

    protected $casts = [
        'purchase_date' => 'datetime',
        'total' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function details()
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    public static function generatePurchaseNumber()
    {
        $date = now()->format('Ymd');
        $lastPurchase = self::whereDate('created_at', now())->latest()->first();
        $sequence = $lastPurchase ? (int) substr($lastPurchase->purchase_number, -4) + 1 : 1;
        return 'PUR' . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
