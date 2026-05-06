@extends('layouts.app')
@section('title', 'Terms of Service - DeliverEats')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="de-card">
                <div class="de-card-body p-md-5">
                    <h1 style="font-weight: 800; color: #fff; margin-bottom: 2rem;">Terms of Service</h1>
                    
                    <div style="color: #cbd5e1; line-height: 1.8;">
                        <p>Last updated: {{ date('F d, Y') }}</p>
                        
                        <h4 style="color: #fff; margin-top: 2rem; margin-bottom: 1rem;">1. Acceptance of Terms</h4>
                        <p>By accessing and using DeliverEats, you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to abide by these terms, please do not use this service.</p>

                        <h4 style="color: #fff; margin-top: 2rem; margin-bottom: 1rem;">2. Description of Service</h4>
                        <p>DeliverEats provides an online platform connecting customers with restaurants and delivery riders. We act as an intermediary for food ordering and delivery services.</p>

                        <h4 style="color: #fff; margin-top: 2rem; margin-bottom: 1rem;">3. User Accounts</h4>
                        <p>When you create an account with us, you must provide us information that is accurate, complete, and current at all times. Failure to do so constitutes a breach of the Terms, which may result in immediate termination of your account on our Service.</p>
                        <p>You are responsible for safeguarding the password that you use to access the Service and for any activities or actions under your password.</p>

                        <h4 style="color: #fff; margin-top: 2rem; margin-bottom: 1rem;">4. Orders and Payments</h4>
                        <p>All orders are subject to availability and confirmation of the order price. Delivery times are estimates and cannot be guaranteed. Payments must be completed successfully before an order is dispatched to the restaurant.</p>

                        <h4 style="color: #fff; margin-top: 2rem; margin-bottom: 1rem;">5. Intellectual Property</h4>
                        <p>The Service and its original content, features and functionality are and will remain the exclusive property of DeliverEats and its licensors. The Service is protected by copyright, trademark, and other laws.</p>

                        <h4 style="color: #fff; margin-top: 2rem; margin-bottom: 1rem;">6. Changes to Terms</h4>
                        <p>We reserve the right, at our sole discretion, to modify or replace these Terms at any time. We will provide notice of any significant changes.</p>

                        <h4 style="color: #fff; margin-top: 2rem; margin-bottom: 1rem;">7. Contact Information</h4>
                        <p>If you have any questions about these Terms, please contact us via our <a href="{{ route('help') }}" style="color: var(--de-primary);">Help Center</a>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
