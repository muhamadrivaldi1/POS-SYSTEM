<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
    'warehouse_id',
    'price_tier_id',
    'transaction_date',
    'subtotal',
    'discount_percentage',
    'discount_amount',
    'voucher_code',
    'tax_percentage',
    'tax_amount',
    'total',
    'payment_method',
    'payment_amount',
    'change_amount',
    'status',
    'notes',
    'stock_override',
    'approved_by',
];


    protected $casts = [
        'transaction_date' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'payment_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function priceTier()
    {
        return $this->belongsTo(PriceTier::class);
    }

    public function details()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function returns()
    {
        return $this->hasMany(ReturnModel::class);
    }

    public static function generateTransactionNumber()
    {
        $date = now()->format('Ymd');
        $lastTransaction = self::whereDate('created_at', now())->latest()->first();
        $sequence = $lastTransaction ? (int) substr($lastTransaction->transaction_number, -4) + 1 : 1;
        return 'TRX' . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function cashSession()
    {
        return $this->belongsTo(CashSession::class);
    }
}
