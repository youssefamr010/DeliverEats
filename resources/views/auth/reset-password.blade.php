@extends('layouts.app')
@section('title', 'Reset Password - DeliverEats')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center min-vh-75 align-items-center">
        <div class="col-lg-5">
            <div class="de-card animate-enter">
                <div class="de-card-body p-5">
                    <div class="text-center mb-5">
                        <div class="mb-4 d-inline-flex align-items-center justify-content-center" 
                             style="width: 80px; height: 80px; background: rgba(16, 185, 129, 0.1); border-radius: 24px;">
                            <i class="fas fa-lock-open text-success fs-1"></i>
                        </div>
                        <h1 class="h2 fw-black text-white mb-2">Secure Your Account</h1>
                        <p class="text-muted">Identity verified. Please choose a new secure password.</p>
                    </div>

                    <form action="{{ route('password.update') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label text-white-50 small fw-bold text-uppercase tracking-wider">New Password</label>
                            <div class="position-relative">
                                <i class="fas fa-key position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                                <input type="password" name="password" class="form-control de-input ps-5 @error('password') is-invalid @enderror" 
                                       placeholder="••••••••" required autofocus>
                            </div>
                            @error('password')
                                <div class="invalid-feedback mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-5">
                            <label class="form-label text-white-50 small fw-bold text-uppercase tracking-wider">Confirm Password</label>
                            <div class="position-relative">
                                <i class="fas fa-check-double position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                                <input type="password" name="password_confirmation" class="form-control de-input ps-5" 
                                       placeholder="••••••••" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-de w-100 py-3 mb-4">
                            Reset & Login <i class="fas fa-shield-alt ms-2"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
