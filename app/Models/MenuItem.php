<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'name', 'description', 'base_price', 'image',
        'is_available', 'is_featured', 'prep_time',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'is_available' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function restaurant()
    {
        return $this->hasOneThrough(Restaurant::class, Category::class, 'id', 'id', 'category_id', 'restaurant_id');
    }

    public function variants()
    {
        return $this->hasMany(ItemVariant::class);
    }

    public function availableVariants()
    {
        return $this->variants()->where('is_available', true);
    }
}
