<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Rider;
use App\Models\Restaurant;
use App\Models\Payout;
use App\Services\RiderDispatchService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_orders'    => Order::count(),
            'active_orders'   => Order::whereNotIn('status', ['delivered', 'cancelled', 'rejected'])->count(),
            'delivered_today' => Order::where('status', 'delivered')->whereDate('delivered_at', today())->count(),
            'total_revenue'   => Payout::sum('platform_amount'),
            'total_restaurants' => Restaurant::count(),
            'total_riders'    => Rider::count(),
            'online_riders'   => Rider::where('is_online', true)->count(),
        ];

        $activeOrders = Order::whereNotIn('status', ['delivered', 'cancelled', 'rejected'])
            ->with(['restaurant', 'rider.user', 'customer'])
            ->latest()->take(20)->get();

        $riders = Rider::where('is_online', true)->with('user')->get();

        return view('admin.dashboard', compact('stats', 'activeOrders', 'riders'));
    }

    public function orders(Request $request)
    {
        $query = Order::with(['restaurant', 'rider.user', 'customer']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $orders = $query->latest()->paginate(20);
        return view('admin.orders', compact('orders'));
    }

    public function revenue()
    {
        $payouts = Payout::with('order.restaurant')
            ->latest()->paginate(20);

        $summary = [
            'total_revenue'     => Payout::sum('platform_amount'),
            'restaurant_total'  => Payout::sum('restaurant_amount'),
            'rider_total'       => Payout::sum('rider_amount'),
            'total_orders'      => Payout::count(),
        ];

        return view('admin.revenue', compact('payouts', 'summary'));
    }

    public function liveMap()
    {
        $activeOrders = Order::whereNotIn('status', ['delivered', 'cancelled', 'rejected'])
            ->with(['restaurant', 'rider.user'])->get();
        $onlineRiders = Rider::where('is_online', true)->with('user')->get();
        $restaurants = Restaurant::where('is_active', true)->get();

        return view('admin.live-map', compact('activeOrders', 'onlineRiders', 'restaurants'));
    }

    public function liveData()
    {
        $activeOrders = Order::whereNotIn('status', ['delivered', 'cancelled', 'rejected'])
            ->with(['restaurant', 'rider.user'])->get();
        $onlineRiders = Rider::where('is_online', true)->with('user')->get();
        $restaurants = Restaurant::where('is_active', true)->get();

        return response()->json([
            'orders' => $activeOrders->map(fn($o) => [
                'id' => $o->id, 'status' => $o->status,
                'restaurant_id' => $o->restaurant_id,
                'rider_id' => $o->rider_id,
                'delivery_lat' => $o->delivery_lat, 'delivery_lng' => $o->delivery_lng,
            ]),
            'riders' => $onlineRiders->map(fn($r) => [
                'id' => $r->id, 'name' => $r->user->name,
                'lat' => $r->current_lat, 'lng' => $r->current_lng,
                'is_available' => $r->is_available,
            ]),
            'restaurants' => $restaurants->map(fn($res) => [
                'id' => $res->id, 'name' => $res->name,
                'lat' => $res->lat, 'lng' => $res->lng,
                'is_open' => $res->is_open,
            ]),
        ]);
    }

    public function feedbacks()
    {
        $feedbacks = \App\Models\Feedback::with('user')->latest()->paginate(15);
        return view('admin.feedbacks', compact('feedbacks'));
    }

    public function resolveFeedback(\App\Models\Feedback $feedback)
    {
        $feedback->update(['status' => 'resolved']);
        return back()->with('success', 'Feedback marked as resolved.');
    }

    public function reviews()
    {
        $ratings = \App\Models\Rating::with(['user', 'review', 'rateable', 'order'])->latest()->paginate(20);
        return view('admin.reviews', compact('ratings'));
    }

    public function payments(Request $request)
    {
        $query = \App\Models\Transaction::with(['user', 'order.restaurant']);

        if ($request->filled('customer_id')) {
            $query->where('user_id', $request->customer_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        $transactions = $query->latest()->paginate(20);
        $customers = \App\Models\User::where('role', 'customer')->get();

        return view('admin.payments', compact('transactions', 'customers'));
    }
}
