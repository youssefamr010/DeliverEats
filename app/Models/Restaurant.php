<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id', 'stripe_connect_id', 'name', 'description', 'address', 'lat', 'lng',
        'phone', 'image', 'cuisine_type', 'commission_rate', 'min_order_amount',
        'delivery_fee', 'avg_prep_time', 'rating_avg', 'rating_count',
        'is_active', 'is_open', 'opens_at', 'closes_at',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'commission_rate' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'rating_avg' => 'decimal:2',
            'is_active' => 'boolean',
            'is_open' => 'boolean',
            'opens_at' => 'datetime:H:i',
            'closes_at' => 'datetime:H:i',
        ];
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function categories()
    {
        return $this->hasMany(Category::class)->orderBy('sort_order');
    }

    public function menuItems()
    {
        return $this->hasManyThrough(MenuItem::class, Category::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function ratings()
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    public function surgePricingLogs()
    {
        return $this->hasMany(SurgePricingLog::class);
    }

    public function payouts()
    {
        return $this->hasManyThrough(Payout::class, Order::class);
    }

    /**
     * Chefs assigned to this restaurant
     */
    public function chefs()
    {
        return $this->hasMany(User::class, 'restaurant_id')->where('role', 'chef');
    }

    /**
     * Riders assigned to this restaurant
     */
    public function assignedRiders()
    {
        return $this->hasMany(User::class, 'restaurant_id')->where('role', 'rider');
    }

    /**
     * All staff (chefs + riders) assigned to this restaurant
     */
    public function staff()
    {
        return $this->hasMany(User::class, 'restaurant_id')->whereIn('role', ['chef', 'rider']);
    }

    public function updateRatingAverage(): void
    {
        $this->rating_avg = $this->ratings()->avg('score') ?? 0;
        $this->rating_count = $this->ratings()->count();
        $this->save();
    }

    public function isCurrentlyOpen(): bool
    {
        if (!$this->is_active || !$this->is_open) return false;
        if (!$this->opens_at || !$this->closes_at) return $this->is_open;

        $now = now()->format('H:i:s');
        return $now >= $this->opens_at && $now <= $this->closes_at;
    }
}
