@extends('layouts.app')
@section('title', 'Forgot Password - DeliverEats')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center min-vh-75 align-items-center">
        <div class="col-lg-5">
            <div class="de-card animate-enter">
                <div class="de-card-body p-5">
                    <div class="text-center mb-5">
                        <div class="mb-4 d-inline-flex align-items-center justify-content-center" 
                             style="width: 80px; height: 80px; background: rgba(99, 102, 241, 0.1); border-radius: 24px;">
                            <i class="fas fa-fingerprint text-primary fs-1"></i>
                        </div>
                        <h1 class="h2 fw-black text-white mb-2">Identify Yourself</h1>
                        <p class="text-muted">Enter the name associated with your account to verify your identity.</p>
                    </div>

                    <form action="{{ route('password.name.verify') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label text-white-50 small fw-bold text-uppercase tracking-wider">Account Name</label>
                            <div class="position-relative">
                                <i class="fas fa-user position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                                <input type="text" name="name" class="form-control de-input ps-5 @error('name') is-invalid @enderror" 
                                       placeholder="Enter your full name" required autofocus>
                            </div>
                            @error('name')
                                <div class="invalid-feedback mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-de w-100 py-3 mb-4">
                            Verify Identity <i class="fas fa-chevron-right ms-2"></i>
                        </button>

                        <div class="text-center">
                            <a href="{{ route('login') }}" class="text-decoration-none text-muted small">
                                <i class="fas fa-arrow-left me-2"></i> Back to Login
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
