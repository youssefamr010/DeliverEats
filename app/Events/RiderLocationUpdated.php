<?php

namespace App\Events;

use App\Models\Rider;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RiderLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $rider;
    public $lat;
    public $lng;

    /**
     * Create a new event instance.
     */
    public function __construct(Rider $rider, float $lat, float $lng)
    {
        $this->rider = $rider;
        $this->lat = $lat;
        $this->lng = $lng;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Broadcast to any order this rider is currently delivering
        $channels = [new PrivateChannel('admin.riders')];
        
        $activeOrder = $this->rider->orders()->whereIn('status', ['confirmed', 'preparing', 'ready_for_pickup', 'on_the_way'])->first();
        
        if ($activeOrder) {
            $channels[] = new PrivateChannel('orders.' . $activeOrder->id);
        }

        return $channels;
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'rider_id' => $this->rider->id,
            'lat'      => $this->lat,
            'lng'      => $this->lng,
            'updated_at' => now()->toDateTimeString(),
        ];
    }
}
