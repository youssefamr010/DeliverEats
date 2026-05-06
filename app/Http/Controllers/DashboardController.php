<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use App\Models\Order;
use App\Models\Payout;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        return match($user->role) {
            'admin'            => redirect()->route('admin.dashboard'),
            'restaurant_owner' => redirect()->route('restaurant.dashboard'),
            'rider'            => redirect()->route('rider.dashboard'),
            'chef'             => redirect()->route('chef.dashboard'),
            default            => redirect()->route('restaurants.index'),
        };
    }

    public function restaurantRevenue(Request $request)
    {
        $restaurant = auth()->user()->restaurant;
        if (!$restaurant) abort(404);

        $period = $request->input('period', '30days');
        $paymentService = new PaymentService();
        $revenue = $paymentService->getRestaurantRevenue($restaurant, $period);

        $recentPayouts = Payout::whereHas('order', fn($q) => $q->where('restaurant_id', $restaurant->id))
            ->with('order')->latest()->take(20)->get();

        $chartData = $this->getChartData($restaurant->id, 'restaurant');

        return view('dashboard.restaurant-revenue', compact('restaurant', 'revenue', 'recentPayouts', 'chartData', 'period'));
    }

    public function riderEarnings(Request $request)
    {
        $rider = auth()->user()->rider;
        if (!$rider) abort(404);

        $period = $request->input('period', '30days');
        $paymentService = new PaymentService();
        $earnings = $paymentService->getRiderEarnings($rider->id, $period);

        $recentPayouts = Payout::whereHas('order', fn($q) => $q->where('rider_id', $rider->id))
            ->with('order.restaurant')->latest()->take(20)->get();

        return view('dashboard.rider-earnings', compact('rider', 'earnings', 'recentPayouts', 'period'));
    }

    private function getChartData(int $entityId, string $type): array
    {
        $days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $query = Payout::whereDate('created_at', $date);

            if ($type === 'restaurant') {
                $query->whereHas('order', fn($q) => $q->where('restaurant_id', $entityId));
                $amount = $query->sum('restaurant_amount');
            } else {
                $query->whereHas('order', fn($q) => $q->where('rider_id', $entityId));
                $amount = $query->sum('rider_amount');
            }

            $days->push(['date' => now()->subDays($i)->format('M d'), 'amount' => round($amount, 2)]);
        }

        return $days->toArray();
    }
}
