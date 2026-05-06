<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'restaurant_id', 'phone', 'avatar', 'address', 'lat', 'lng', 'wallet_balance',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
        ];
    }

    // --- Relationships ---

    /**
     * Restaurant owned by this user (for restaurant_owner role)
     */
    public function ownedRestaurant()
    {
        return $this->hasOne(Restaurant::class, 'owner_id');
    }

    /**
     * Alias for backward compatibility
     */
    public function restaurant()
    {
        if ($this->role === 'restaurant_owner') {
            return $this->hasOne(Restaurant::class, 'owner_id');
        }
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Restaurant this chef/rider is assigned to
     */
    public function assignedRestaurant()
    {
        return $this->belongsTo(Restaurant::class, 'restaurant_id');
    }

    public function rider()
    {
        return $this->hasOne(Rider::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // --- Helpers ---

    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isRestaurantOwner(): bool { return $this->role === 'restaurant_owner'; }
    public function isRider(): bool { return $this->role === 'rider'; }
    public function isCustomer(): bool { return $this->role === 'customer'; }
    public function isChef(): bool { return $this->role === 'chef'; }

    public function unreadNotifications()
    {
        return $this->notifications()->whereNull('read_at');
    }

    /**
     * Get the restaurant this user belongs to (works for owner, chef, rider)
     */
    public function getRestaurantForStaff(): ?Restaurant
    {
        if ($this->role === 'restaurant_owner') {
            return $this->ownedRestaurant;
        }
        return $this->assignedRestaurant;
    }
}
