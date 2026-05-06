<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rider extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'stripe_connect_id', 'current_lat', 'current_lng', 'is_available', 'is_online',
        'vehicle_type', 'rating_avg', 'rating_count', 'total_deliveries', 'total_earnings',
    ];

    protected function casts(): array
    {
        return [
            'current_lat' => 'decimal:7',
            'current_lng' => 'decimal:7',
            'is_available' => 'boolean',
            'is_online' => 'boolean',
            'rating_avg' => 'decimal:2',
            'total_earnings' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dispatches()
    {
        return $this->hasMany(RiderDispatch::class);
    }

    public function activeOrders()
    {
        return $this->hasMany(Order::class)->whereNotIn('status', ['delivered', 'cancelled', 'rejected']);
    }

    public function completedOrders()
    {
        return $this->hasMany(Order::class)->where('status', 'delivered');
    }

    public function ratings()
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    public function updateRatingAverage(): void
    {
        $this->rating_avg = $this->ratings()->avg('score') ?? 0;
        $this->rating_count = $this->ratings()->count();
        $this->save();
    }

    /**
     * Calculate distance to a point using Haversine formula
     */
    public function distanceTo(float $lat, float $lng): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat - $this->current_lat);
        $dLng = deg2rad($lng - $this->current_lng);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($this->current_lat)) * cos(deg2rad($lat)) *
             sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($earthRadius * $c, 2);
    }
}
