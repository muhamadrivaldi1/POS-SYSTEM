<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnModel extends Model
{
    use HasFactory;

    protected $table = 'returns';

    protected $fillable = [
        'return_number',
        'transaction_id',
        'user_id',
        'approved_by',
        'return_date',
        'total',
        'status',
        'reason'
    ];

    protected $casts = [
        'return_date' => 'datetime',
        'total' => 'decimal:2',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function details()
    {
        return $this->hasMany(ReturnDetail::class, 'return_id');
    }

    public static function generateReturnNumber()
    {
        $date = now()->format('Ymd');
        $lastReturn = self::whereDate('created_at', now())->latest()->first();
        $sequence = $lastReturn ? (int) substr($lastReturn->return_number, -4) + 1 : 1;
        return 'RET' . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
