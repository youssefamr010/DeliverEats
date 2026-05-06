<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\MenuItem;
use App\Models\OrderItem;
use App\Models\Restaurant;
use App\Pricing\SurgePricingEngine;
use App\Services\PaymentService;
use App\StateMachine\OrderStateMachine;
use Illuminate\Http\Request;

class OrderApiController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'restaurant_id'      => 'required|exists:restaurants,id',
            'items'              => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.variant_id' => 'nullable|exists:item_variants,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'delivery_address'   => 'required|string',
            'payment_method'     => 'required|in:cash,card',
            'tip'                => 'nullable|numeric|min:0',
        ]);

        $restaurant = Restaurant::findOrFail($validated['restaurant_id']);
        $surgeEngine = new SurgePricingEngine();
        $surgeInfo = $surgeEngine->calculateSurge($restaurant);

        $subtotal = 0;
        $orderItems = [];
        foreach ($validated['items'] as $item) {
            $menuItem = MenuItem::findOrFail($item['menu_item_id']);
            $unitPrice = (float)$menuItem->base_price;
            if (!empty($item['variant_id'])) {
                $variant = $menuItem->variants()->findOrFail($item['variant_id']);
                $unitPrice += (float)$variant->price_modifier;
            }
            $itemSub = $unitPrice * $item['quantity'];
            $subtotal += $itemSub;
            $orderItems[] = [
                'menu_item_id' => $item['menu_item_id'],
                'item_variant_id' => $item['variant_id'] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $unitPrice,
                'subtotal' => $itemSub,
            ];
        }

        $deliveryFee = (float)$restaurant->delivery_fee;
        $surgeFee = $surgeInfo['multiplier'] > 1 ? round($deliveryFee * ($surgeInfo['multiplier'] - 1), 2) : 0;
        $tip = (float)($validated['tip'] ?? 0);
        $tax = round($subtotal * 0.05, 2);
        $total = round($subtotal + $deliveryFee + $surgeFee + $tax + $tip, 2);

        $order = Order::create([
            'customer_id' => auth()->id(),
            'restaurant_id' => $restaurant->id,
            'status' => 'placed',
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'surge_multiplier' => $surgeInfo['multiplier'],
            'surge_fee' => $surgeFee,
            'tax' => $tax, 'tip' => $tip,
            'total' => $total,
            'delivery_address' => $validated['delivery_address'],
            'payment_method'   => $validated['payment_method'],
            'payment_status'   => 'pending',
        ]);

        // Create order items
        foreach ($orderItems as $oi) {
            $oi['order_id'] = $order->id;
            OrderItem::create($oi);
        }

        // Handle Wallet Payment
        if ($validated['payment_method'] === 'wallet') {
            $user = auth()->user();
            if ($user->wallet_balance >= $total) {
                $user->decrement('wallet_balance', $total);
                $order->update(['payment_status' => 'paid']);
                
                \App\Models\Transaction::create([
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'amount' => $total,
                    'type' => 'payment',
                    'method' => 'wallet',
                    'status' => 'completed',
                    'description' => 'Order Payment (API) #' . $order->id,
                ]);
            } else {
                $order->delete();
                return response()->json(['error' => 'Insufficient wallet balance.'], 400);
            }
        }

        // Log initial state
        \App\Models\OrderStateLog::create([
            'order_id'        => $order->id,
            'from_state'      => null,
            'to_state'        => 'placed',
            'actor_type'      => 'customer',
            'actor_id'        => auth()->id(),
            'transitioned_at' => now(),
        ]);

        // Stripe Redirection Info
        $paymentUrl = null;
        if ($validated['payment_method'] === 'card' && $order->payment_status === 'pending') {
            // In API we might return a checkout URL or simulated success
            $secret = config('services.stripe.secret');
            if (empty($secret) || str_contains($secret, '_here')) {
                $order->update(['payment_status' => 'paid']);
            } else {
                // For a real API, we'd return a Stripe Session URL or PaymentIntent client secret
                // Here we'll return a placeholder or simulate success if requested
                $order->update(['payment_status' => 'paid']); // Simulating for now
            }
        }

        return response()->json([
            'order' => $order->load('items'),
            'payment_status' => $order->payment_status,
            'payment_url' => $paymentUrl
        ], 201);
    }

    public function show(Order $order)
    {
        return response()->json($order->load(['items.menuItem', 'restaurant', 'rider.user', 'stateLogs']));
    }

    public function track(Order $order)
    {
        return response()->json([
            'status' => $order->status,
            'rider' => $order->rider ? [
                'name' => $order->rider->user->name,
                'lat' => $order->rider->current_lat,
                'lng' => $order->rider->current_lng,
            ] : null,
            'state_logs' => $order->stateLogs,
        ]);
    }

    public function history(Request $request)
    {
        $orders = Order::where('customer_id', auth()->id())
            ->with('restaurant')->latest()->paginate(15);
        return response()->json($orders);
    }
}
