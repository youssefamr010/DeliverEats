<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\Rider;
use App\Models\Restaurant;
use Livewire\Component;
use Livewire\Attributes\On;

class AdminControlTower extends Component
{
    public $activeOrdersCount = 0;
    public $availableRidersCount = 0;
    public $totalRevenueToday = 0;
    public $totalRevenueAllTime = 0;
    public $estimatedProfitAllTime = 0;
    public $recentOrders = [];
    public $paymentIssuesCount = 0;

    public function mount()
    {
        $this->loadStats();
    }

    #[On('echo:admin.orders,OrderStatusUpdated')]
    public function onOrderStatusUpdated($event)
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        // Active orders are confirmed, preparing, or on the way
        $this->activeOrdersCount = Order::whereIn('status', ['confirmed', 'preparing', 'ready_for_pickup', 'on_the_way'])->count();
        
        // Orders stuck in pending payment for more than 5 minutes
        $this->paymentIssuesCount = Order::where('status', 'pending_payment')
            ->where('created_at', '<', now()->subMinutes(5))
            ->count();

        $this->availableRidersCount = Rider::where('is_online', true)->where('is_available', true)->count();
        
        // Calculate Platform Revenue: Commission + Surge Fees + Tax from paid orders
        $paidOrdersToday = Order::whereDate('created_at', today())
            ->whereIn('payment_status', ['paid', 'completed'])
            ->get();

        $this->totalRevenueToday = $paidOrdersToday->sum(function($order) {
            $commission = $order->subtotal * ($order->restaurant->commission_rate / 100);
            return $commission + $order->surge_fee + $order->tax;
        });

        // Revenue All Time - Use Payouts for historical accuracy
        $this->totalRevenueAllTime = \App\Models\Payout::sum('platform_amount');

        // Estimated Profit: 70% of Platform Revenue (after 30% operational costs)
        $this->estimatedProfitAllTime = $this->totalRevenueAllTime * 0.70;
        
        $this->recentOrders = Order::with(['restaurant', 'customer'])
            ->whereNotIn('status', ['pending_payment'])
            ->latest()
            ->take(15)
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.admin-control-tower');
    }
}
