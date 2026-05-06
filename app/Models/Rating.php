<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'order_id', 'rateable_type', 'rateable_id', 'score'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function rateable()
    {
        return $this->morphTo();
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }
}
