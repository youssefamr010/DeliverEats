<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'order_total', 'restaurant_amount', 'rider_amount',
        'platform_amount', 'platform_commission_pct', 'status', 'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'order_total' => 'decimal:2',
            'restaurant_amount' => 'decimal:2',
            'rider_amount' => 'decimal:2',
            'platform_amount' => 'decimal:2',
            'platform_commission_pct' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
