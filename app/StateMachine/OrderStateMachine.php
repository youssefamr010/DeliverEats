<?php

namespace App\StateMachine;

use App\Models\Order;
use App\Models\OrderStateLog;
use InvalidArgumentException;

/**
 * Finite State Machine for Order transitions.
 * Guards prevent invalid state jumps. Every transition is logged (event sourcing).
 */
class OrderStateMachine
{
    /**
     * Allowed transitions: from_state => [to_states]
     */
    private const TRANSITIONS = [
        'pending_payment'  => ['placed', 'cancelled'],
        'placed'           => ['confirmed', 'cancelled', 'rejected'],
        'confirmed'        => ['preparing', 'cancelled', 'rejected', 'on_the_way'],
        'preparing'        => ['ready_for_pickup', 'cancelled', 'on_the_way'],
        'ready_for_pickup' => ['on_the_way'],
        'on_the_way'       => ['delivered', 'cancelled'],
        'delivered'        => [], // Terminal state
        'cancelled'        => [], // Terminal state
        'rejected'         => [], // Terminal state
    ];

    /**
     * Who can trigger each transition
     */
    private const TRANSITION_ACTORS = [
        'pending_payment'  => ['customer', 'system'],
        'placed'           => ['customer', 'system', 'admin'],
        'confirmed'        => ['restaurant', 'chef', 'system', 'admin'],
        'preparing'        => ['restaurant', 'chef', 'admin'],
        'ready_for_pickup' => ['restaurant', 'chef', 'admin'],
        'on_the_way'       => ['rider', 'restaurant', 'system', 'admin'],
        'delivered'        => ['rider', 'restaurant', 'system', 'admin'],
        'cancelled'        => ['customer', 'restaurant', 'chef', 'rider', 'system', 'admin'],
        'rejected'         => ['restaurant', 'admin'],
    ];

    /**
     * Check if a transition is valid
     */
    public static function canTransition(string $from, string $to): bool
    {
        if (!isset(self::TRANSITIONS[$from])) {
            return false;
        }
        return in_array($to, self::TRANSITIONS[$from]);
    }

    /**
     * Get allowed next states from current state
     */
    public static function getAllowedTransitions(string $currentState): array
    {
        return self::TRANSITIONS[$currentState] ?? [];
    }

    /**
     * Transition an order to a new state with full validation and logging
     *
     * @throws InvalidArgumentException if transition is invalid
     */
    public static function transition(
        Order $order,
        string $toState,
        string $actorType = 'system',
        ?int $actorId = null,
        array $metadata = []
    ): Order {
        $fromState = $order->status;

        // Guard: prevent invalid transitions
        if (!self::canTransition($fromState, $toState)) {
            throw new InvalidArgumentException(
                "Invalid order state transition: {$fromState} → {$toState}. " .
                "Allowed transitions from '{$fromState}': " . implode(', ', self::getAllowedTransitions($fromState))
            );
        }

        // Guard: check actor permissions
        if (!in_array($actorType, self::TRANSITION_ACTORS[$toState] ?? ['system'])) {
            throw new InvalidArgumentException(
                "Actor type '{$actorType}' is not allowed to transition order to '{$toState}'."
            );
        }

        // Perform the transition
        $order->status = $toState;

        // Set delivered_at timestamp when delivered
        if ($toState === 'delivered') {
            $order->delivered_at = now();
            $order->payment_status = 'completed';
        }

        $order->save();

        // Event sourcing: log every state change with timestamp and actor
        OrderStateLog::create([
            'order_id'        => $order->id,
            'from_state'      => $fromState,
            'to_state'        => $toState,
            'actor_type'      => $actorType,
            'actor_id'        => $actorId,
            'metadata'        => $metadata,
            'transitioned_at' => now(),
        ]);

        // Real-time notification
        \App\Events\OrderStatusUpdated::dispatch($order);

        // Notify customer
        \App\Jobs\SendNotificationJob::dispatch(
            $order->customer_id,
            "Order Status Updated",
            "Your order #{$order->id} is now {$toState}.",
            'order_update',
            ['order_id' => $order->id, 'status' => $toState]
        );

        // Recalculate surge when an order is placed or delivered
        if (in_array($toState, ['placed', 'delivered', 'cancelled', 'rejected'])) {
            \App\Jobs\RecalculateSurgeJob::dispatch($order->restaurant);
        }

        // Process payment asynchronously on delivery
        if ($toState === 'delivered') {
            \App\Jobs\ProcessPaymentJob::dispatch($order);
        }

        return $order;
    }

    /**
     * Get all valid states
     */
    public static function getAllStates(): array
    {
        return array_keys(self::TRANSITIONS);
    }

    /**
     * Check if a state is terminal (no further transitions)
     */
    public static function isTerminal(string $state): bool
    {
        return empty(self::TRANSITIONS[$state] ?? []);
    }

    /**
     * Get the full transition map for documentation/diagrams
     */
    public static function getTransitionMap(): array
    {
        return self::TRANSITIONS;
    }
}
