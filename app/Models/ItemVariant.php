<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemVariant extends Model
{
    use HasFactory;

    protected $fillable = ['menu_item_id', 'name', 'price_modifier', 'is_available'];

    protected function casts(): array
    {
        return [
            'price_modifier' => 'decimal:2',
            'is_available' => 'boolean',
        ];
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }

    /**
     * Calculate the total price (base + modifier)
     */
    public function getTotalPriceAttribute(): float
    {
        return $this->menuItem->base_price + $this->price_modifier;
    }
}
