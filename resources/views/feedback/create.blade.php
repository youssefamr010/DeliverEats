@extends('layouts.app')
@section('title', 'Submit Feedback - DeliverEats')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="de-card animate-enter">
                <div class="de-card-body p-md-5">
                    <div class="text-center mb-4">
                        <div style="width: 60px; height: 60px; background: rgba(234, 179, 8, 0.15); border-radius: 16px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                            <i class="fas fa-comment-dots" style="font-size: 1.5rem; color: var(--de-gold);"></i>
                        </div>
                        <h2 style="font-weight: 800; color: #fff;">Submit Feedback or Complaint</h2>
                        <p style="color: #cbd5e1;">We're here to help and improve your experience.</p>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success" style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #34d399; border-radius: 12px; padding: 1rem;">
                            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #f87171; border-radius: 12px; padding: 1rem;">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('feedback.store') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Your Name</label>
                                <input type="text" name="name" class="form-control de-input" value="{{ auth()->check() ? auth()->user()->name : old('name') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control de-input" value="{{ auth()->check() ? auth()->user()->email : old('email') }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Subject</label>
                                <select name="subject" class="form-select de-input" required>
                                    <option value="">Select a subject...</option>
                                    <option value="General Inquiry">General Inquiry</option>
                                    <option value="Complaint">Complaint</option>
                                    <option value="App Feedback">App Feedback</option>
                                    <option value="Restaurant/Rider Issue">Restaurant/Rider Issue</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Message</label>
                                <textarea name="message" class="form-control de-input" rows="5" placeholder="Please describe your issue or feedback in detail..." required>{{ old('message') }}</textarea>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-de-gold w-100 py-3">
                                    <i class="fas fa-paper-plane me-2"></i> Send Feedback
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
