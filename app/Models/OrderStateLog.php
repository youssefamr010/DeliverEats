<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStateLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'from_state', 'to_state',
        'actor_type', 'actor_id', 'metadata', 'transitioned_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'transitioned_at' => 'datetime',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the actor (user) who triggered this transition
     */
    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function getFormattedTransitionAttribute(): string
    {
        $from = $this->from_state ?? 'new';
        return ucfirst(str_replace('_', ' ', $from)) . ' → ' . ucfirst(str_replace('_', ' ', $this->to_state));
    }
}
