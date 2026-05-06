<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Rider;
use App\Models\RiderDispatch;
use App\Models\Payout;
use App\Services\RiderDispatchService;
use Illuminate\Http\Request;

class RiderController extends Controller
{
    /**
     * Rider dashboard with stats, dispatches, active orders, and map
     */
    public function dashboard()
    {
        $rider = Rider::where('user_id', auth()->id())->firstOrFail();

        // Today's completed deliveries
        $completedToday = Order::where('rider_id', $rider->id)
            ->where('status', 'delivered')
            ->whereDate('delivered_at', today())
            ->count();

        // Today's earnings from payouts
        $todayPayouts = Payout::whereHas('order', fn($q) => $q->where('rider_id', $rider->id)->whereDate('delivered_at', today()))->sum('rider_amount');

        // Also add tips from today's delivered orders
        $todayTips = Order::where('rider_id', $rider->id)->where('status', 'delivered')->whereDate('delivered_at', today())->sum('tip');

        $earnings = [
            'total_earnings' => $todayPayouts + $todayTips,
            'delivery_fee_earnings' => $todayPayouts,
            'tip_earnings' => $todayTips,
            'total_deliveries' => $completedToday,
        ];

        // Monthly earnings
        $monthlyPayouts = Payout::whereHas('order', fn($q) => $q->where('rider_id', $rider->id)->whereMonth('delivered_at', now()->month))->sum('rider_amount');
        $monthlyTips = Order::where('rider_id', $rider->id)->where('status', 'delivered')->whereMonth('delivered_at', now()->month)->sum('tip');
        $monthlyEarnings = [
            'total_earnings' => $monthlyPayouts + $monthlyTips,
        ];

        // All-time earnings
        $allTimePayouts = Payout::whereHas('order', fn($q) => $q->where('rider_id', $rider->id))->sum('rider_amount');
        $allTimeTips = Order::where('rider_id', $rider->id)->where('status', 'delivered')->sum('tip');
        $allTimeEarnings = $allTimePayouts + $allTimeTips;

        // Pending dispatches (assigned to this rider, not yet accepted or rejected)
        $pendingDispatches = RiderDispatch::where('rider_id', $rider->id)
            ->whereNull('accepted_at')
            ->whereNull('rejected_at')
            ->with(['order.restaurant'])
            ->latest()
            ->get();

        // Active orders assigned to this rider
        $activeOrders = Order::where('rider_id', $rider->id)
            ->whereNotIn('status', ['delivered', 'cancelled', 'rejected'])
            ->with('restaurant')
            ->latest()
            ->get();

        return view('rider.dashboard', compact('rider', 'completedToday', 'earnings', 'monthlyEarnings', 'allTimeEarnings', 'pendingDispatches', 'activeOrders'));
    }

    /**
     * Toggle rider online/offline status
     */
    public function toggleOnline()
    {
        $rider = Rider::where('user_id', auth()->id())->firstOrFail();
        $rider->is_online = !$rider->is_online;

        // When going offline, also mark as unavailable
        if (!$rider->is_online) {
            $rider->is_available = false;
        } else {
            $rider->is_available = true;
        }

        $rider->save();

        return back()->with('success', $rider->is_online ? 'You are now online!' : 'You are now offline.');
    }

    /**
     * Update rider GPS location
     */
    public function updateLocation(Request $request)
    {
        $validated = $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $rider = Rider::where('user_id', auth()->id())->firstOrFail();
        $rider->update([
            'current_lat' => $validated['lat'],
            'current_lng' => $validated['lng'],
        ]);

        return response()->json(['status' => 'ok']);
    }

    /**
     * Accept a dispatch assignment
     */
    public function acceptDispatch(RiderDispatch $dispatch)
    {
        if ($dispatch->rider_id !== Rider::where('user_id', auth()->id())->value('id')) {
            abort(403);
        }

        $dispatch->update(['accepted_at' => now()]);

        // Assign rider to the order
        $order = $dispatch->order;
        $order->rider_id = $dispatch->rider_id;
        $order->save();

        // Transition status to on_the_way immediately
        try {
            \App\StateMachine\OrderStateMachine::transition($order, 'on_the_way', 'rider', auth()->id());
        } catch (\Exception $e) {
            // Log error or ignore if already in a further state (unlikely here)
        }

        // Mark rider as unavailable
        $dispatch->rider->update(['is_available' => false]);

        return back()->with('success', 'Dispatch accepted! You are now on the way for Order #' . $order->id);
    }

    /**
     * Reject a dispatch assignment — re-dispatch to next nearest rider
     */
    public function rejectDispatch(RiderDispatch $dispatch)
    {
        if ($dispatch->rider_id !== Rider::where('user_id', auth()->id())->value('id')) {
            abort(403);
        }

        $dispatch->update([
            'rejected_at' => now(),
        ]);

        // Try to dispatch to the next nearest rider
        $dispatchService = new RiderDispatchService();
        $dispatchService->dispatchRider($dispatch->order);

        return back()->with('success', 'Dispatch rejected. Re-dispatching to next rider.');
    }
}
