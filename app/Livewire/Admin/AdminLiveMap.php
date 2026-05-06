<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\Rider;
use Livewire\Component;
use Livewire\Attributes\On;

class AdminLiveMap extends Component
{
    public $orders = [];
    public $riders = [];

    public function mount()
    {
        $this->loadData();
    }

    #[On('echo:admin.orders,OrderStatusUpdated')]
    public function onOrderStatusUpdated($event)
    {
        $this->loadData();
    }

    #[On('echo:admin.riders,RiderLocationUpdated')]
    public function onRiderLocationUpdated($event)
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->orders = Order::whereNotIn('status', ['delivered', 'cancelled', 'rejected'])
            ->with(['restaurant', 'rider'])
            ->get()
            ->map(fn($order) => [
                'id' => $order->id,
                'status' => $order->status,
                'lat' => $order->restaurant->lat,
                'lng' => $order->restaurant->lng,
                'restaurant_name' => $order->restaurant->name,
                'rider_name' => $order->rider?->name ?? 'Unassigned',
            ])->toArray();

        $this->riders = Rider::where('is_online', true)
            ->get()
            ->map(fn($rider) => [
                'id' => $rider->id,
                'name' => $rider->name,
                'lat' => $rider->current_lat,
                'lng' => $rider->current_lng,
                'is_available' => $rider->is_available,
            ])->toArray();
    }

    public function render()
    {
        return view('livewire.admin.admin-live-map');
    }
}
