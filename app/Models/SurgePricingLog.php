<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurgePricingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id', 'area', 'multiplier', 'strategy', 'reason',
        'factors', 'triggered_at', 'expired_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'multiplier' => 'decimal:2',
            'factors' => 'array',
            'triggered_at' => 'datetime',
            'expired_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function isExpired(): bool
    {
        return $this->expired_at && $this->expired_at->isPast();
    }
}
