<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Restaurant;
use App\StateMachine\OrderStateMachine;
use Illuminate\Http\Request;

class ChefController extends Controller
{
    /**
     * Chef kitchen dashboard — shows orders for chef's assigned restaurant
     */
    public function dashboard()
    {
        $user = auth()->user();
        $restaurant = Restaurant::find($user->restaurant_id);

        if (!$restaurant) {
            return view('chef.no-restaurant');
        }

        // Orders waiting to be prepared (confirmed)
        $incomingOrders = Order::where('restaurant_id', $restaurant->id)
            ->whereIn('status', ['placed', 'confirmed'])
            ->with(['customer', 'items.menuItem'])
            ->latest()
            ->get();

        // Currently preparing
        $preparingOrders = Order::where('restaurant_id', $restaurant->id)
            ->where('status', 'preparing')
            ->with(['customer', 'items.menuItem'])
            ->latest()
            ->get();

        // Ready for pickup
        $readyOrders = Order::where('restaurant_id', $restaurant->id)
            ->where('status', 'ready_for_pickup')
            ->with(['customer', 'items.menuItem', 'rider.user'])
            ->latest()
            ->get();

        // Today's completed count
        $completedToday = Order::where('restaurant_id', $restaurant->id)
            ->where('status', 'delivered')
            ->whereDate('delivered_at', today())
            ->count();

        return view('chef.dashboard', compact('restaurant', 'incomingOrders', 'preparingOrders', 'readyOrders', 'completedToday'));
    }
}
