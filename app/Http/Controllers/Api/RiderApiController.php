<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rider;
use App\Models\RiderDispatch;
use App\Services\RiderDispatchService;
use App\StateMachine\OrderStateMachine;
use Illuminate\Http\Request;

class RiderApiController extends Controller
{
    public function updateLocation(Request $request)
    {
        $validated = $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $rider = auth()->user()->rider;
        $rider->update(['current_lat' => $validated['lat'], 'current_lng' => $validated['lng']]);

        // Dispatch broadcasting event
        \App\Events\RiderLocationUpdated::dispatch($rider, (float)$validated['lat'], (float)$validated['lng']);

        return response()->json(['success' => true]);
    }

    public function toggleOnline()
    {
        $rider = auth()->user()->rider;
        $rider->update([
            'is_online' => !$rider->is_online,
            'is_available' => !$rider->is_online,
        ]);
        return response()->json(['is_online' => $rider->is_online]);
    }

    public function acceptDispatch(RiderDispatch $dispatch)
    {
        $service = app(RiderDispatchService::class);
        $service->acceptDispatch($dispatch);
        return response()->json(['success' => true]);
    }

    public function rejectDispatch(Request $request, RiderDispatch $dispatch)
    {
        $service = app(RiderDispatchService::class);
        $service->rejectDispatch($dispatch, $request->input('reason', ''));
        return response()->json(['success' => true]);
    }

    public function updateOrderStatus(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'status'   => 'required|string',
        ]);

        $order = \App\Models\Order::findOrFail($validated['order_id']);

        try {
            OrderStateMachine::transition($order, $validated['status'], 'rider', auth()->id());
            if ($validated['status'] === 'delivered') {
                $service = app(RiderDispatchService::class);
                $service->completeDelivery($order);
            }
            return response()->json(['success' => true, 'status' => $order->status]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
