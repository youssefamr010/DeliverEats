<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Restaurant;
use App\Models\MenuItem;
use App\Pricing\SurgePricingEngine;
use App\Services\PaymentService;
use App\Services\RiderDispatchService;
use App\StateMachine\OrderStateMachine;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Show order form / cart for a restaurant
     */
    public function create(Restaurant $restaurant)
    {
        $restaurant->load(['categories.menuItems.variants' => fn($q) => $q->where('is_available', true)]);

        // Check surge pricing
        $surgeEngine = new SurgePricingEngine();
        $surgeInfo = $surgeEngine->calculateSurge($restaurant);

        return view('orders.create', compact('restaurant', 'surgeInfo'));
    }

    /**
     * Place a new order
     */
    public function store(Request $request, Restaurant $restaurant)
    {
        $validated = $request->validate([
            'items'              => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.variant_id' => 'nullable|exists:item_variants,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'delivery_address'   => 'required|string|max:500',
            'notes'              => 'nullable|string|max:1000',
            'payment_method'     => 'required|in:cash,card',
            'tip'                => 'nullable|numeric|min:0',
        ]);

        // Calculate surge
        $surgeEngine = new SurgePricingEngine();
        $surgeInfo = $surgeEngine->calculateSurge($restaurant);
        $surgeMultiplier = $surgeInfo['multiplier'];

        // Calculate subtotal
        $subtotal = 0;
        $orderItems = [];

        foreach ($validated['items'] as $item) {
            $menuItem = MenuItem::findOrFail($item['menu_item_id']);
            $unitPrice = (float)$menuItem->base_price;

            if (!empty($item['variant_id'])) {
                $variant = $menuItem->variants()->findOrFail($item['variant_id']);
                $unitPrice += (float)$variant->price_modifier;
            }

            $itemSubtotal = $unitPrice * $item['quantity'];
            $subtotal += $itemSubtotal;

            $orderItems[] = [
                'menu_item_id'       => $item['menu_item_id'],
                'item_variant_id'    => $item['variant_id'] ?? null,
                'quantity'           => $item['quantity'],
                'unit_price'         => $unitPrice,
                'subtotal'           => $itemSubtotal,
            ];
        }

        $deliveryFee = (float)$restaurant->delivery_fee;
        $surgeFee = $surgeMultiplier > 1 ? round($deliveryFee * ($surgeMultiplier - 1), 2) : 0;
        $tip = (float)($validated['tip'] ?? 0);
        $tax = round($subtotal * 0.05, 2); // 5% tax
        $total = round($subtotal + $deliveryFee + $surgeFee + $tax + $tip, 2);

        $initialStatus = $validated['payment_method'] === 'card' ? 'pending_payment' : 'placed';

        // Create order
        $order = Order::create([
            'customer_id'      => auth()->id(),
            'restaurant_id'    => $restaurant->id,
            'status'           => $initialStatus,
            'subtotal'         => $subtotal,
            'delivery_fee'     => $deliveryFee,
            'surge_multiplier' => $surgeMultiplier,
            'surge_fee'        => $surgeFee,
            'tax'              => $tax,
            'tip'              => $tip,
            'total'            => $total,
            'delivery_address' => $validated['delivery_address'],
            'notes'            => $validated['notes'] ?? null,
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
                    'description' => 'Order Payment #' . $order->id,
                ]);
            } else {
                $order->delete();
                return back()->with('error', 'Insufficient wallet balance.');
            }
        }

        // Log initial state
        \App\Models\OrderStateLog::create([
            'order_id'        => $order->id,
            'from_state'      => null,
            'to_state'        => $initialStatus,
            'actor_type'      => 'customer',
            'actor_id'        => auth()->id(),
            'transitioned_at' => now(),
        ]);

        // Log surge if applicable
        if ($surgeMultiplier > 1) {
            $surgeEngine->calculateAndLog($restaurant);
        }

        // Recalculate surge for the area
        \App\Jobs\RecalculateSurgeJob::dispatch($restaurant);

        // If card payment, create PaymentIntent and redirect to Stripe
        if ($validated['payment_method'] === 'card' && $order->payment_status === 'pending') {
            $paymentService = app(\App\Services\PaymentService::class);
            $paymentService->createPaymentIntent($order);
            
            return app(\App\Http\Controllers\StripeController::class)->checkoutOrder($order);
        }

        return redirect()->route('orders.track', $order)->with('success', 'Order placed successfully!');
    }

    /**
     * Track an order in real-time
     */
    public function track(Order $order)
    {
        $this->authorizeAccess($order);

        $order->load(['restaurant', 'rider.user', 'items.menuItem', 'items.variant', 'stateLogs']);

        $allowedTransitions = OrderStateMachine::getAllowedTransitions($order->status);

        return view('orders.track', compact('order', 'allowedTransitions'));
    }

    /**
     * Transition order status (restaurant/rider action)
     */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'reason' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        $actorType = match($user->role) {
            'restaurant_owner' => 'restaurant',
            'chef'             => 'chef',
            'rider'            => 'rider',
            'admin'            => 'admin',
            default            => 'customer',
        };

        try {
            OrderStateMachine::transition(
                $order,
                $validated['status'],
                $actorType,
                $user->id,
                ($validated['reason'] ?? null) ? ['reason' => $validated['reason']] : []
            );

            // Auto-dispatch rider when order is confirmed
            if ($validated['status'] === 'confirmed') {
                \App\Jobs\DispatchRiderJob::dispatch($order);
            }

            // Note: ProcessPaymentJob is now handled inside OrderStateMachine::transition()
            // when state moves to 'delivered' to ensure it's always processed.

            if ($validated['status'] === 'delivered') {
                $dispatchService = app(\App\Services\RiderDispatchService::class);
                $dispatchService->completeDelivery($order);
            }

            return back()->with('success', 'Order status updated to: ' . ucfirst(str_replace('_', ' ', $validated['status'])));
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Customer order history
     */
    public function history()
    {
        $orders = Order::where('customer_id', auth()->id())
            ->with(['restaurant', 'items'])
            ->latest()
            ->paginate(10);

        return view('orders.history', compact('orders'));
    }

    /**
     * API: Get order status (for AJAX polling)
     */
    public function getStatus(Order $order)
    {
        $order->load(['rider.user', 'stateLogs']);

        return response()->json([
            'status'        => $order->status,
            'status_label'  => ucfirst(str_replace('_', ' ', $order->status)),
            'rider'         => $order->rider ? [
                'name' => $order->rider->user->name,
                'lat'  => $order->rider->current_lat,
                'lng'  => $order->rider->current_lng,
            ] : null,
            'delivered_at'  => $order->delivered_at?->format('H:i'),
            'state_logs'    => $order->stateLogs->map(fn($log) => [
                'from'  => $log->from_state,
                'to'    => $log->to_state,
                'actor' => $log->actor_type,
                'time'  => $log->transitioned_at->format('H:i:s'),
            ]),
        ]);
    }

    private function authorizeAccess(Order $order)
    {
        $user = auth()->user();
        if ($user->role === 'admin') return;
        if ($order->customer_id === $user->id) return;
        if ($user->restaurant && $order->restaurant_id === $user->restaurant->id) return;
        if ($user->rider && $order->rider_id === $user->rider->id) return;
        abort(403);
    }
}
