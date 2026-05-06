<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiderDispatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'rider_id', 'distance_km',
        'dispatched_at', 'accepted_at', 'rejected_at', 'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'distance_km' => 'decimal:2',
            'dispatched_at' => 'datetime',
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function rider()
    {
        return $this->belongsTo(Rider::class);
    }

    public function isPending(): bool
    {
        return is_null($this->accepted_at) && is_null($this->rejected_at);
    }
}
