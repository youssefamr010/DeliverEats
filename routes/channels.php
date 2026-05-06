<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('orders.{orderId}', function ($user, $orderId) {
    $order = \App\Models\Order::find($orderId);
    if (!$order) return false;
    
    // Customer who placed the order
    if ($user->role === 'customer' && $order->customer_id === $user->id) return true;
    
    // Rider assigned to the order
    if ($user->role === 'rider' && $order->rider_id === $user->rider?->id) return true;
    
    // Admin or Restaurant owner
    return in_array($user->role, ['admin', 'restaurant_owner']);
});

Broadcast::channel('admin.orders', function ($user) {
    return $user->role === 'admin';
});

Broadcast::channel('admin.riders', function ($user) {
    return $user->role === 'admin';
});
