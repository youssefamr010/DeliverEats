<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Rider;
use App\Models\Restaurant;
use App\Models\User;
use App\Models\OrderItem;
use App\StateMachine\OrderStateMachine;
use App\Pricing\Strategies\MultiplierSurgeStrategy;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AdminSimulationController extends Controller
{
    public function index()
    {
        return view('admin.simulations');
    }

    public function volumeSpike()
    {
        $customers = User::where('role', 'customer')->get();
        $restaurants = Restaurant::where('is_active', true)->where('is_open', true)->get();
        $riders = Rider::where('is_online', true)->where('is_available', true)->get();

        if ($customers->isEmpty() || $restaurants->isEmpty()) {
            return back()->with('error', 'Need more customers/restaurants to simulate.');
        }

        $created = 0;
        DB::transaction(function() use ($customers, $restaurants, $riders, &$created) {
            for ($i = 0; $i < 50; $i++) {
                $customer = $customers->random();
                $restaurant = $restaurants->random();
                $item = $restaurant->menuItems()->first();

                if (!$item) continue;

                $order = Order::create([
                    'customer_id' => $customer->id,
                    'restaurant_id' => $restaurant->id,
                    'status' => 'placed',
                    'subtotal' => $item->base_price,
                    'delivery_fee' => $restaurant->delivery_fee,
                    'tax' => $item->base_price * 0.1,
                    'total' => $item->base_price + $restaurant->delivery_fee + ($item->base_price * 0.1),
                    'delivery_address' => $customer->address ?? 'Simulated Address',
                ]);

                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $item->id,
                    'quantity' => 1,
                    'unit_price' => $item->base_price,
                    'subtotal' => $item->base_price,
                ]);

                // Auto-confirm and assign if riders available
                if ($riders->count() > $created) {
                    $rider = $riders[$created];
                    OrderStateMachine::transition($order, 'confirmed');
                    $order->update(['rider_id' => $rider->id]);
                    $rider->update(['is_available' => false]);
                }

                $created++;
            }
        });

        return back()->with('success', "Simulated $created orders successfully.");
    }

    public function testStateMachine()
    {
        $customer = User::where('role', 'customer')->first() ?? User::factory()->create(['role' => 'customer']);
        $restaurant = Restaurant::first();

        if (!$restaurant) {
            return back()->with('error', 'No restaurants available to test.');
        }

        $results = [];

        // Scenario 1: Forbidden Jump (delivered → preparing)
        // Correct behavior: THROW Exception
        $order1 = Order::create([
            'customer_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'status' => 'delivered',
            'subtotal' => 80,
            'delivery_fee' => 20,
            'total' => 100,
            'delivery_address' => 'Integrity Test'
        ]);

        try {
            OrderStateMachine::transition($order1, 'preparing', 'admin');
            $results[] = [
                'test' => 'Integrity: [Delivered] → [Preparing]',
                'status' => 'FAIL',
                'message' => 'CRITICAL: The system allowed a transition from a terminal state (Delivered) back to Preparing. This violates state immutability.'
            ];
        } catch (InvalidArgumentException $e) {
            $results[] = [
                'test' => 'Integrity: [Delivered] → [Preparing]',
                'status' => 'PASS',
                'message' => 'SUCCESS: State machine correctly blocked illegal rollback. Exception: ' . $e->getMessage()
            ];
        }

        // Scenario 2: Forbidden Skip (placed → delivered)
        // Correct behavior: THROW Exception
        $order2 = Order::create([
            'customer_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'status' => 'placed',
            'subtotal' => 80,
            'delivery_fee' => 20,
            'total' => 100,
            'delivery_address' => 'Integrity Test'
        ]);

        try {
            OrderStateMachine::transition($order2, 'delivered', 'admin');
            $results[] = [
                'test' => 'Integrity: [Placed] → [Delivered]',
                'status' => 'FAIL',
                'message' => 'CRITICAL: The system allowed skipping mandatory steps (Confirmed, Preparing, etc.).'
            ];
        } catch (InvalidArgumentException $e) {
            $results[] = [
                'test' => 'Integrity: [Placed] → [Delivered]',
                'status' => 'PASS',
                'message' => 'SUCCESS: State machine correctly enforced sequential flow. Exception: ' . $e->getMessage()
            ];
        }

        // Scenario 3: Actor Permission Violation (customer trying to "confirm" order)
        // Correct behavior: THROW Exception
        try {
            OrderStateMachine::transition($order2, 'confirmed', 'customer');
            $results[] = [
                'test' => 'Security: [Customer] → [Confirm Order]',
                'status' => 'FAIL',
                'message' => 'CRITICAL: A customer was able to confirm their own order.'
            ];
        } catch (InvalidArgumentException $e) {
            $results[] = [
                'test' => 'Security: [Customer] → [Confirm Order]',
                'status' => 'PASS',
                'message' => 'SUCCESS: Correctly blocked unauthorized actor. Exception: ' . $e->getMessage()
            ];
        }

        return back()->with('test_results', $results);
    }

    public function testSurge()
    {
        $strategy = new MultiplierSurgeStrategy();
        
        $results = [
            'normal' => $strategy->calculate(['demand' => 2, 'available_riders' => 10]),
            'spike'  => $strategy->calculate(['demand' => 50, 'available_riders' => 1]),
            'rollback' => $strategy->calculate(['demand' => 2, 'available_riders' => 10]),
        ];

        return back()->with('surge_results', $results);
    }

    public function testPaymentSplits()
    {
        $service = app(PaymentService::class);
        $restaurants = Restaurant::take(3)->get();
        $results = [];

        foreach ($restaurants as $res) {
            // Test with different commission rates
            $rate = rand(10, 25);
            
            $calc = $service->calculateSplits(
                orderTotal: 1000,
                subtotal: 800,
                deliveryFee: 150,
                surgeFee: 50,
                tip: 0,
                commissionRate: (float)$rate
            );
            $results[] = [
                'restaurant' => $res->name,
                'commission' => $rate . '%',
                'total' => '1000.00',
                'subtotal' => '800.00',
                'restaurant_gets' => $calc['restaurant_amount'],
                'rider_gets' => $calc['rider_amount'],
                'platform_gets' => $calc['platform_amount'],
            ];
        }

    }

    public function cleanup()
    {
        // Finish all active orders
        Order::whereNotIn('status', ['delivered', 'cancelled', 'rejected'])
            ->update([
                'status' => 'delivered',
                'delivered_at' => now(),
                'payment_status' => 'completed'
            ]);

        // Reset all riders to available
        Rider::query()->update([
            'is_available' => true,
            'is_online' => true
        ]);

        return back()->with('success', 'System cleared: All orders finalized and riders reset to available.');
    }
}
