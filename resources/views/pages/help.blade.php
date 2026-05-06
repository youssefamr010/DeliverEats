@extends('layouts.app')
@section('title', 'Help Center - DeliverEats')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center mb-5">
            <h1 style="font-weight: 800; font-size: 3rem; color: #fff;">Help Center</h1>
            <p style="color: #cbd5e1; font-size: 1.1rem;">How can we help you today?</p>
        </div>
    </div>

    <div class="row g-4 justify-content-center">
        <!-- FAQ Section -->
        <div class="col-md-5">
            <div class="de-card h-100">
                <div class="de-card-body">
                    <div style="width: 50px; height: 50px; background: rgba(59, 130, 246, 0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                        <i class="fas fa-question-circle" style="font-size: 1.5rem; color: var(--de-primary);"></i>
                    </div>
                    <h3 style="font-weight: 700; color: #fff; margin-bottom: 1rem;">Frequently Asked Questions</h3>
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item" style="background: transparent; border-color: rgba(255,255,255,0.1);">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1" style="background: transparent; color: #fff; font-weight: 600;">
                                    How do I track my order?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    You can track your order in real-time by navigating to the "My Orders" tab and clicking "View" on any active order.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item" style="background: transparent; border-color: rgba(255,255,255,0.1);">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2" style="background: transparent; color: #fff; font-weight: 600;">
                                    What are the delivery fees?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    Delivery fees are set individually by each restaurant and usually range between 15 LE and 40 LE depending on distance.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Feedback & Complaints -->
        <div class="col-md-5">
            <div class="de-card h-100" style="border-color: rgba(234, 179, 8, 0.3);">
                <div class="de-card-body">
                    <div style="width: 50px; height: 50px; background: rgba(234, 179, 8, 0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                        <i class="fas fa-comment-dots" style="font-size: 1.5rem; color: var(--de-gold);"></i>
                    </div>
                    <h3 style="font-weight: 700; color: #fff; margin-bottom: 1rem;">Feedback & Complaints</h3>
                    <p style="color: #cbd5e1; margin-bottom: 2rem;">
                        Having an issue with an order, or want to share a suggestion? We value your feedback and our team is ready to help resolve any complaints immediately.
                    </p>
                    <a href="{{ route('feedback.create') }}" class="btn btn-de-gold w-100">
                        <i class="fas fa-paper-plane me-2"></i> Submit Feedback
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .accordion-button::after {
        filter: invert(1);
    }
    .accordion-button:not(.collapsed) {
        background: rgba(255,255,255,0.05) !important;
        color: var(--de-primary) !important;
        box-shadow: none;
    }
</style>
@endsection
