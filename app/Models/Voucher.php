<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'value',
        'min_purchase',
        'max_usage',
        'used_count',
        'valid_from',
        'valid_until',
        'status'
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_purchase' => 'decimal:2',
        'max_usage' => 'integer',
        'used_count' => 'integer',
        'valid_from' => 'date',
        'valid_until' => 'date',
    ];

    public function isValid($subtotal)
    {
        $now = now()->toDateString();
        return $this->status === 'active'
            && $this->valid_from <= $now
            && $this->valid_until >= $now
            && $subtotal >= $this->min_purchase
            && ($this->max_usage == 0 || $this->used_count < $this->max_usage);
    }

    public function calculateDiscount($subtotal)
    {
        if ($this->type === 'percentage') {
            return ($subtotal * $this->value) / 100;
        }
        return $this->value;
    }
}
