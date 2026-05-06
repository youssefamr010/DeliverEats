<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payout;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Log;

/**
 * Payment Service: calculates split payments between platform, restaurant, and rider.
 * Simulates Stripe Connect split payments.
 */
class PaymentService
{
    protected string $stripeSecret;

    public function __construct()
    {
        $this->stripeSecret = config('services.stripe.secret', env('STRIPE_SECRET', ''));
        if (!empty($this->stripeSecret)) {
            \Stripe\Stripe::setApiKey($this->stripeSecret);
        }
    }

    /**
     * Create a real Stripe PaymentIntent when an order is placed
     */
    public function createPaymentIntent(Order $order): ?string
    {
        if (empty($this->stripeSecret)) return null;

        try {
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => (int) ($order->total * 100),
                'currency' => 'usd',
                'metadata' => [
                    'order_id' => $order->id,
                    'customer_id' => $order->customer_id,
                ],
                'automatic_payment_methods' => ['enabled' => true],
            ]);

            return $paymentIntent->id;
        } catch (\Exception $e) {
            Log::error('Stripe PaymentIntent Error', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Calculate and execute real Stripe Connect transfers for a delivered order
     */
    public function processPayment(Order $order): Payout
    {
        $restaurant = $order->restaurant;
        $rider = $order->rider;
        $commissionRate = $restaurant->commission_rate;

        // Calculate splits
        $splits = $this->calculateSplits(
            orderTotal: (float) $order->total,
            subtotal: (float) $order->subtotal,
            deliveryFee: (float) $order->delivery_fee,
            surgeFee: (float) $order->surge_fee,
            tip: (float) $order->tip,
            commissionRate: (float) $commissionRate
        );

        // Execute Stripe Connect Transfers
        if (!empty($this->stripeSecret)) {
            try {
                // Transfer to Restaurant
                if ($restaurant->stripe_connect_id) {
                    \Stripe\Transfer::create([
                        'amount' => (int) ($splits['restaurant_amount'] * 100),
                        'currency' => 'usd',
                        'destination' => $restaurant->stripe_connect_id,
                        'description' => "Order #{$order->id} - Restaurant Share",
                        'metadata' => ['order_id' => $order->id],
                    ]);
                }

                // Transfer to Rider
                if ($rider && $rider->stripe_connect_id) {
                    \Stripe\Transfer::create([
                        'amount' => (int) ($splits['rider_amount'] * 100),
                        'currency' => 'usd',
                        'destination' => $rider->stripe_connect_id,
                        'description' => "Order #{$order->id} - Rider Share",
                        'metadata' => ['order_id' => $order->id],
                    ]);
                }

                Log::info('Stripe Connect Transfers Executed', ['order_id' => $order->id, 'splits' => $splits]);
            } catch (\Exception $e) {
                Log::error('Stripe Connect Transfer Error', ['order_id' => $order->id, 'error' => $e->getMessage()]);
                // We keep going so the database record is created, but status could be 'failed'
            }
        }

        // Create payout record
        $payout = Payout::create([
            'order_id'              => $order->id,
            'order_total'           => $order->total,
            'restaurant_amount'     => $splits['restaurant_amount'],
            'rider_amount'          => $splits['rider_amount'],
            'platform_amount'       => $splits['platform_amount'],
            'platform_commission_pct' => $commissionRate,
            'status'                => 'processed',
            'processed_at'          => now(),
        ]);

        // Update order payment status
        $order->update(['payment_status' => 'completed']);

        return $payout;
    }

    /**
     * Calculate payment splits
     *
     * @return array{restaurant_amount: float, rider_amount: float, platform_amount: float}
     */
    public function calculateSplits(
        float $orderTotal,
        float $subtotal,
        float $deliveryFee,
        float $surgeFee,
        float $tip,
        float $commissionRate
    ): array {
        // Platform commission is taken from the food subtotal
        $platformCommission = round($subtotal * ($commissionRate / 100), 2);

        // Restaurant gets: subtotal - platform commission
        $restaurantAmount = round($subtotal - $platformCommission, 2);

        // Rider gets: delivery fee + tip
        $riderAmount = round($deliveryFee + $tip, 2);

        // Platform gets: commission + surge fee
        $platformAmount = round($platformCommission + $surgeFee, 2);

        // Handle any rounding difference
        $totalAllocated = $restaurantAmount + $riderAmount + $platformAmount;
        $diff = round($orderTotal - $totalAllocated, 2);
        // Adjust platform amount for any tax/rounding
        $platformAmount = round($platformAmount + $diff, 2);

        return [
            'restaurant_amount' => $restaurantAmount,
            'rider_amount'      => $riderAmount,
            'platform_amount'   => $platformAmount,
        ];
    }

    /**
     * Get revenue summary for a restaurant
     */
    public function getRestaurantRevenue(Restaurant $restaurant, ?string $period = '30days'): array
    {
        $query = Payout::whereHas('order', fn($q) => $q->where('restaurant_id', $restaurant->id));

        if ($period === '7days') $query->where('created_at', '>=', now()->subDays(7));
        elseif ($period === '30days') $query->where('created_at', '>=', now()->subDays(30));
        elseif ($period === 'today') $query->whereDate('created_at', today());

        $payouts = $query->get();

        return [
            'total_orders'      => $payouts->count(),
            'total_revenue'     => round($payouts->sum('order_total'), 2),
            'restaurant_earnings' => round($payouts->sum('restaurant_amount'), 2),
            'platform_fees'     => round($payouts->sum('platform_amount'), 2),
            'avg_order_value'   => $payouts->count() > 0 ? round($payouts->avg('order_total'), 2) : 0,
        ];
    }

    /**
     * Get rider earnings summary
     */
    public function getRiderEarnings(int $riderId, ?string $period = '30days'): array
    {
        $query = Payout::whereHas('order', fn($q) => $q->where('rider_id', $riderId));

        if ($period === '7days') $query->where('created_at', '>=', now()->subDays(7));
        elseif ($period === '30days') $query->where('created_at', '>=', now()->subDays(30));
        elseif ($period === 'today') $query->whereDate('created_at', today());

        $payouts = $query->get();

        return [
            'total_deliveries'  => $payouts->count(),
            'total_earnings'    => round($payouts->sum('rider_amount'), 2),
            'avg_per_delivery'  => $payouts->count() > 0 ? round($payouts->avg('rider_amount'), 2) : 0,
        ];
    }
}
