<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Rider;
use App\Models\User;
use App\Services\RiderDispatchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiderDispatchTest extends TestCase
{
    use RefreshDatabase;

    private function setup_dispatch(): array
    {
        $customer = User::create(['name' => 'Customer', 'email' => 'c@test.com', 'password' => bcrypt('pw'), 'role' => 'customer']);
        $owner = User::create(['name' => 'Owner', 'email' => 'o@test.com', 'password' => bcrypt('pw'), 'role' => 'restaurant_owner']);
        $restaurant = Restaurant::create(['owner_id' => $owner->id, 'name' => 'Test', 'address' => '123 St', 'lat' => 31.9500, 'lng' => 35.9100]);

        // Create riders at different distances
        $nearUser = User::create(['name' => 'Near', 'email' => 'near@test.com', 'password' => bcrypt('pw'), 'role' => 'rider']);
        $nearRider = Rider::create(['user_id' => $nearUser->id, 'current_lat' => 31.9510, 'current_lng' => 35.9110, 'is_online' => true, 'is_available' => true]);

        $farUser = User::create(['name' => 'Far', 'email' => 'far@test.com', 'password' => bcrypt('pw'), 'role' => 'rider']);
        $farRider = Rider::create(['user_id' => $farUser->id, 'current_lat' => 31.9800, 'current_lng' => 35.9500, 'is_online' => true, 'is_available' => true]);

        $order = Order::create([
            'customer_id' => $customer->id, 'restaurant_id' => $restaurant->id,
            'status' => 'placed', 'subtotal' => 20, 'delivery_fee' => 2.5, 'total' => 23.5,
            'delivery_address' => '456 St',
        ]);

        return [$order, $nearRider, $farRider, $restaurant];
    }

    /** @test */
    public function dispatches_nearest_rider(): void
    {
        [$order, $nearRider, $farRider] = $this->setup_dispatch();
        $service = app(RiderDispatchService::class);
        $dispatch = $service->dispatchRider($order);

        $this->assertNotNull($dispatch);
        $this->assertEquals($nearRider->id, $dispatch->rider_id);
    }

    /** @test */
    public function marks_rider_as_unavailable_after_dispatch(): void
    {
        [$order, $nearRider] = $this->setup_dispatch();
        $service = app(RiderDispatchService::class);
        $service->dispatchRider($order);

        $this->assertFalse($nearRider->fresh()->is_available);
    }

    /** @test */
    public function skips_offline_riders(): void
    {
        [$order, $nearRider, $farRider] = $this->setup_dispatch();
        $nearRider->update(['is_online' => false]);

        $service = app(RiderDispatchService::class);
        $dispatch = $service->dispatchRider($order);

        $this->assertEquals($farRider->id, $dispatch->rider_id);
    }

    /** @test */
    public function returns_null_when_no_riders_available(): void
    {
        [$order, $nearRider, $farRider] = $this->setup_dispatch();
        $nearRider->update(['is_online' => false]);
        $farRider->update(['is_online' => false]);

        $service = app(RiderDispatchService::class);
        $dispatch = $service->dispatchRider($order);

        $this->assertNull($dispatch);
    }
}
