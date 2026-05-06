<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Webhook;

class StripeController extends Controller
{
    /**
     * Create a Checkout Session for an Order
     */
    public function checkoutOrder(Order $order)
    {
        $secret = config('services.stripe.secret');
        // Check if key is empty
        if (empty($secret) || str_contains($secret, 'your_secret_key')) {
            Log::error('Stripe Secret Key missing for Order #' . $order->id);
            return back()->with('error', 'Stripe is not properly configured.');
        }

        Stripe::setApiKey($secret);

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'Order #' . $order->id . ' from ' . $order->restaurant->name,
                    ],
                    'unit_amount' => $order->total * 100,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('stripe.success', ['type' => 'order', 'id' => $order->id]) . '&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('stripe.cancel', ['type' => 'order', 'id' => $order->id]),
            'client_reference_id' => $order->id,
            'customer_email' => auth()->user()->email,
            'metadata' => [
                'type' => 'order_payment',
                'order_id' => $order->id,
                'user_id' => auth()->id(),
            ],
        ]);

        return redirect($session->url);
    }

    /**
     * Create a Checkout Session for Wallet Top Up
     */
    public function topUpWallet(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:5']);
        $amount = $request->amount;

        $secret = config('services.stripe.secret');
        if (empty($secret) || str_contains($secret, 'your_secret_key')) {
            Log::error('Stripe Secret Key missing for Wallet Top-up');
            return back()->with('error', 'Stripe is not properly configured.');
        }

        Stripe::setApiKey($secret);

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'Wallet Top Up - DeliverEats',
                    ],
                    'unit_amount' => $amount * 100,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('stripe.success', ['type' => 'wallet']) . '&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('stripe.cancel', ['type' => 'wallet']),
            'customer_email' => auth()->user()->email,
            'metadata' => [
                'type' => 'wallet_topup',
                'amount' => $amount,
                'user_id' => auth()->id(),
            ],
        ]);

        return redirect($session->url);
    }

    public function success(Request $request)
    {
        $sessionId = $request->get('session_id');
        $type = $request->get('type');
        
        try {
            $session = Session::retrieve($sessionId);
            
            if ($session->payment_status === 'paid') {
                if ($type === 'wallet') {
                    $amount = $session->metadata->amount;
                    $userId = $session->metadata->user_id;
                    
                    // Logic handled by webhook usually, but we can do a fallback here if needed
                    // For now, redirect with success message
                    return redirect()->route('dashboard')->with('success', 'Wallet topped up successfully!');
                } else {
                    return redirect()->route('orders.track', $request->id)->with('success', 'Payment successful!');
                }
            }
        } catch (\Exception $e) {
            Log::error('Stripe Success Error: ' . $e->getMessage());
        }

        return redirect()->route('dashboard')->with('error', 'Payment verification failed.');
    }

    public function cancel()
    {
        return redirect()->route('dashboard')->with('error', 'Payment was cancelled.');
    }

    /**
     * Handle Stripe Webhook
     */
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $this->handleSuccessfulPayment($session);
        }

        return response()->json(['status' => 'success']);
    }

    protected function handleSuccessfulPayment($session)
    {
        $metadata = $session->metadata;
        $userId = $metadata->user_id;
        $user = User::find($userId);

        if ($metadata->type === 'wallet_topup') {
            $amount = $metadata->amount;
            
            $user->increment('wallet_balance', $amount);
            
            Transaction::create([
                'user_id' => $userId,
                'amount' => $amount,
                'type' => 'deposit',
                'method' => 'stripe',
                'stripe_payment_id' => $session->payment_intent,
                'status' => 'completed',
                'description' => 'Wallet Top Up via Stripe',
            ]);
        } elseif ($metadata->type === 'order_payment') {
            $orderId = $metadata->order_id;
            $order = Order::find($orderId);
            
            $order->update(['payment_status' => 'paid']);

            // Transition from pending_payment to placed
            if ($order->status === 'pending_payment') {
                \App\StateMachine\OrderStateMachine::transition(
                    $order, 
                    'placed', 
                    'system', 
                    null, 
                    ['payment_method' => 'stripe', 'stripe_session' => $session->id]
                );
            }
            
            Transaction::create([
                'user_id' => $userId,
                'order_id' => $orderId,
                'amount' => $order->total,
                'type' => 'payment',
                'method' => 'stripe',
                'stripe_payment_id' => $session->payment_intent,
                'status' => 'completed',
                'description' => 'Order Payment #' . $orderId,
            ]);
        }
    }
}
