@extends('layouts.app')
@section('title', 'Join DeliverEats')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="de-card animate-enter">
                <div class="de-card-body p-5">
                    <div class="text-center mb-5">
                        <div class="mb-4 d-inline-flex align-items-center justify-content-center" 
                             style="width: 80px; height: 80px; background: rgba(139, 92, 246, 0.1); border: 1px solid rgba(139, 92, 246, 0.2); border-radius: 24px;">
                            <i class="fas fa-rocket text-secondary fs-2"></i>
                        </div>
                        <h2 class="text-white fw-black display-6">Create Account</h2>
                        <p class="text-muted">Start your journey with Cairo's elite network.</p>
                    </div>

                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label x-small text-uppercase fw-black letter-spacing-1 opacity-50">Full Name</label>
                                <input type="text" name="name" class="form-control de-input" 
                                       value="{{ old('name') }}" required placeholder="E.g. Ahmed Aly">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label x-small text-uppercase fw-black letter-spacing-1 opacity-50">Email Address</label>
                                <input type="email" name="email" class="form-control de-input" 
                                       value="{{ old('email') }}" required placeholder="name@email.com">
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label x-small text-uppercase fw-black letter-spacing-1 opacity-50 mb-3">Choose Your Role</label>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <input type="radio" name="role" value="customer" id="role_customer" class="btn-check" {{ old('role', request('role', 'customer')) === 'customer' ? 'checked' : '' }}>
                                        <label class="btn-role-select" for="role_customer">
                                            <div class="role-icon text-primary"><i class="fas fa-user"></i></div>
                                            <div class="role-title">Customer</div>
                                            <div class="role-desc">Order gourmet meals</div>
                                        </label>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="radio" name="role" value="restaurant_owner" id="role_restaurant" class="btn-check" {{ old('role', request('role')) === 'restaurant_owner' ? 'checked' : '' }}>
                                        <label class="btn-role-select" for="role_restaurant">
                                            <div class="role-icon text-accent"><i class="fas fa-store"></i></div>
                                            <div class="role-title">Partner</div>
                                            <div class="role-desc">Grow your kitchen</div>
                                        </label>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="radio" name="role" value="rider" id="role_rider" class="btn-check" {{ old('role', request('role')) === 'rider' ? 'checked' : '' }}>
                                        <label class="btn-role-select" for="role_rider">
                                            <div class="role-icon text-secondary"><i class="fas fa-motorcycle"></i></div>
                                            <div class="role-title">Rider</div>
                                            <div class="role-desc">Join our fast fleet</div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label x-small text-uppercase fw-black letter-spacing-1 opacity-50">Password</label>
                                <input type="password" name="password" class="form-control de-input" 
                                       required placeholder="••••••••">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label x-small text-uppercase fw-black letter-spacing-1 opacity-50">Confirm Password</label>
                                <input type="password" name="password_confirmation" class="form-control de-input" 
                                       required placeholder="••••••••">
                            </div>

                            <div class="col-12 mt-5">
                                <button type="submit" class="btn btn-de-gold w-100 py-3">
                                    Initialize Account <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="text-center mt-5">
                        <p class="small text-muted">Already a member? <a href="{{ route('login') }}" class="text-primary fw-bold text-decoration-none">Sign In</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('styles')
<style>
    .btn-role-select {
        display: block;
        padding: 1.5rem;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid var(--de-border);
        border-radius: 20px;
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        text-align: center;
    }
    
    .btn-check:checked + .btn-role-select {
        background: rgba(99, 102, 241, 0.1);
        border-color: var(--de-primary);
        transform: translateY(-5px);
        box-shadow: 0 15px 30px -10px rgba(99, 102, 241, 0.3);
    }
    
    .role-icon { font-size: 1.75rem; margin-bottom: 0.75rem; }
    .role-title { font-weight: 800; color: #fff; margin-bottom: 0.25rem; font-size: 0.9rem; }
    .role-desc { font-size: 0.7rem; color: var(--de-text-muted); line-height: 1.2; }
</style>
@endsection
@endsection

