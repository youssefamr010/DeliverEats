<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id', 'restaurant_id', 'rider_id', 'status',
        'subtotal', 'delivery_fee', 'surge_multiplier', 'surge_fee',
        'tax', 'tip', 'total', 'delivery_address', 'delivery_lat', 'delivery_lng',
        'notes', 'payment_method', 'payment_status',
        'estimated_delivery_at', 'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'surge_multiplier' => 'decimal:2',
            'surge_fee' => 'decimal:2',
            'tax' => 'decimal:2',
            'tip' => 'decimal:2',
            'total' => 'decimal:2',
            'delivery_lat' => 'decimal:7',
            'delivery_lng' => 'decimal:7',
            'estimated_delivery_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    // --- Relationships ---

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function rider()
    {
        return $this->belongsTo(Rider::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function stateLogs()
    {
        return $this->hasMany(OrderStateLog::class)->orderBy('transitioned_at');
    }

    public function dispatches()
    {
        return $this->hasMany(RiderDispatch::class);
    }

    public function payout()
    {
        return $this->hasOne(Payout::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    // --- Helpers ---

    public function isTerminal(): bool
    {
        return in_array($this->status, ['delivered', 'cancelled', 'rejected']);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['placed', 'confirmed']);
    }

    public function getFormattedTotalAttribute(): string
    {
        return '$' . number_format($this->total, 2);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'placed' => 'info',
            'confirmed' => 'primary',
            'preparing' => 'warning',
            'ready_for_pickup' => 'secondary',
            'on_the_way' => 'info',
            'delivered' => 'success',
            'cancelled' => 'danger',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }
}
