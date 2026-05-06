<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Rider;
use App\StateMachine\OrderStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use InvalidArgumentException;

class OrderStateMachineTest extends TestCase
{
    use RefreshDatabase;

    private function createOrder(string $status = 'placed'): Order
    {
        $customer = User::create(['name' => 'Test Customer', 'email' => 'test' . uniqid() . '@test.com', 'password' => bcrypt('password'), 'role' => 'customer']);
        $owner = User::create(['name' => 'Test Owner', 'email' => 'owner' . uniqid() . '@test.com', 'password' => bcrypt('password'), 'role' => 'restaurant_owner']);
        $restaurant = Restaurant::create(['owner_id' => $owner->id, 'name' => 'Test Restaurant', 'address' => '123 Test St', 'lat' => 31.95, 'lng' => 35.91]);

        return Order::create([
            'customer_id' => $customer->id, 'restaurant_id' => $restaurant->id,
            'status' => $status, 'subtotal' => 20.00, 'delivery_fee' => 2.50,
            'total' => 23.50, 'delivery_address' => '456 Test Ave',
        ]);
    }

    /** @test */
    public function valid_transition_placed_to_confirmed(): void
    {
        $order = $this->createOrder('placed');
        OrderStateMachine::transition($order, 'confirmed', 'restaurant');
        $this->assertEquals('confirmed', $order->fresh()->status);
    }

    /** @test */
    public function valid_transition_confirmed_to_preparing(): void
    {
        $order = $this->createOrder('confirmed');
        OrderStateMachine::transition($order, 'preparing', 'restaurant');
        $this->assertEquals('preparing', $order->fresh()->status);
    }

    /** @test */
    public function valid_full_lifecycle(): void
    {
        $order = $this->createOrder('placed');
        OrderStateMachine::transition($order, 'confirmed', 'restaurant');
        OrderStateMachine::transition($order, 'preparing', 'restaurant');
        OrderStateMachine::transition($order, 'ready_for_pickup', 'restaurant');
        OrderStateMachine::transition($order, 'on_the_way', 'rider');
        OrderStateMachine::transition($order, 'delivered', 'rider');
        $this->assertEquals('delivered', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->delivered_at);
    }

    /** @test */
    public function invalid_transition_delivered_to_preparing_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $order = $this->createOrder('delivered');
        OrderStateMachine::transition($order, 'preparing', 'restaurant');
    }

    /** @test */
    public function invalid_transition_cancelled_to_confirmed_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $order = $this->createOrder('cancelled');
        OrderStateMachine::transition($order, 'confirmed', 'restaurant');
    }

    /** @test */
    public function invalid_backward_transition_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $order = $this->createOrder('preparing');
        OrderStateMachine::transition($order, 'placed', 'system');
    }

    /** @test */
    public function state_transition_creates_log_entry(): void
    {
        $order = $this->createOrder('placed');
        OrderStateMachine::transition($order, 'confirmed', 'restaurant', 1);

        $this->assertDatabaseHas('order_state_logs', [
            'order_id' => $order->id,
            'from_state' => 'placed',
            'to_state' => 'confirmed',
            'actor_type' => 'restaurant',
        ]);
    }

    /** @test */
    public function cancellation_from_placed_is_allowed(): void
    {
        $order = $this->createOrder('placed');
        OrderStateMachine::transition($order, 'cancelled', 'customer');
        $this->assertEquals('cancelled', $order->fresh()->status);
    }

    /** @test */
    public function terminal_states_have_no_transitions(): void
    {
        $this->assertEmpty(OrderStateMachine::getAllowedTransitions('delivered'));
        $this->assertEmpty(OrderStateMachine::getAllowedTransitions('cancelled'));
        $this->assertEmpty(OrderStateMachine::getAllowedTransitions('rejected'));
    }
}
