<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Rider;
use App\Models\RiderDispatch;

/**
 * Rider Dispatch Service: assigns nearest available rider to an order.
 * Uses Haversine formula for distance calculation.
 */
class RiderDispatchService
{
    protected GoogleMapsService $googleMaps;

    public function __construct(GoogleMapsService $googleMaps)
    {
        $this->googleMaps = $googleMaps;
    }

    /**
     * Find and assign the nearest available rider for an order
     *
     * @return RiderDispatch|null The dispatch record, or null if no rider available
     */
    public function dispatchRider(Order $order): ?RiderDispatch
    {
        $restaurant = $order->restaurant;

        if (!$restaurant) return null;

        // Find all online, available riders who are not already dispatched for this order
        $existingDispatchRiderIds = RiderDispatch::where('order_id', $order->id)
            ->whereNull('rejected_at')
            ->pluck('rider_id');

        $availableRiders = Rider::where('is_online', true)
            ->where('is_available', true)
            ->whereNotNull('current_lat')
            ->whereNotNull('current_lng')
            ->whereNotIn('id', $existingDispatchRiderIds)
            ->get();

        if ($availableRiders->isEmpty()) return null;

        // Calculate distance from each rider to the restaurant
        $ridersWithDistance = $availableRiders->map(function (Rider $rider) use ($restaurant) {
            // Try Google Maps first, fallback to Haversine
            $distance = $this->googleMaps->getDistance(
                (float)$rider->current_lat, (float)$rider->current_lng,
                (float)$restaurant->lat, (float)$restaurant->lng
            );

            if ($distance === null) {
                $distance = $rider->distanceTo($restaurant->lat, $restaurant->lng);
            }

            return [
                'rider'    => $rider,
                'distance' => $distance,
            ];
        });

        // Sort by distance ascending — nearest first
        $sorted = $ridersWithDistance->sortBy('distance');
        $nearest = $sorted->first();

        if (!$nearest) return null;

        /** @var Rider $selectedRider */
        $selectedRider = $nearest['rider'];
        $distance = $nearest['distance'];

        // Create dispatch record
        $dispatch = RiderDispatch::create([
            'order_id'      => $order->id,
            'rider_id'      => $selectedRider->id,
            'distance_km'   => $distance,
            'dispatched_at' => now(),
        ]);

        // Assign rider to order
        $order->update(['rider_id' => $selectedRider->id]);

        // Mark rider as unavailable (busy)
        $selectedRider->update(['is_available' => false]);

        return $dispatch;
    }

    /**
     * Handle rider accepting a dispatch
     */
    public function acceptDispatch(RiderDispatch $dispatch): void
    {
        $dispatch->update(['accepted_at' => now()]);
    }

    /**
     * Handle rider rejecting a dispatch
     */
    public function rejectDispatch(RiderDispatch $dispatch, string $reason = ''): ?RiderDispatch
    {
        $dispatch->update([
            'rejected_at'      => now(),
            'rejection_reason' => $reason,
        ]);

        // Make rider available again
        $dispatch->rider->update(['is_available' => true]);

        // Remove rider from order
        $dispatch->order->update(['rider_id' => null]);

        // Try to dispatch to next nearest rider
        return $this->dispatchRider($dispatch->order);
    }

    /**
     * Complete a delivery — free up the rider
     */
    public function completeDelivery(Order $order): void
    {
        if ($order->rider) {
            $rider = $order->rider;
            $rider->update([
                'is_available'     => true,
                'total_deliveries' => $rider->total_deliveries + 1,
                'total_earnings'   => $rider->total_earnings + ($order->delivery_fee + $order->tip),
            ]);
        }
    }

    /**
     * Get dispatch statistics
     */
    public function getStats(): array
    {
        return [
            'total_riders'     => Rider::count(),
            'online_riders'    => Rider::where('is_online', true)->count(),
            'available_riders' => Rider::where('is_online', true)->where('is_available', true)->count(),
            'busy_riders'      => Rider::where('is_online', true)->where('is_available', false)->count(),
            'active_dispatches' => RiderDispatch::whereNull('accepted_at')->whereNull('rejected_at')->count(),
        ];
    }
}
