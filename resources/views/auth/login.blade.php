@extends('layouts.app')
@section('title', 'Login - DeliverEats')

@section('content')
<div class="container py-5 mt-lg-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-8">
            <div class="de-card animate-enter">
                <div class="de-card-body p-5">
                    <div class="text-center mb-5">
                        <div class="mb-4 d-inline-flex align-items-center justify-content-center" 
                             style="width: 80px; height: 80px; background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.2); border-radius: 24px;">
                            <i class="fas fa-lock-open text-primary fs-2"></i>
                        </div>
                        <h2 class="text-white fw-black display-6">Welcome Back</h2>
                        <p class="text-muted">Cairo's culinary world awaits you.</p>
                    </div>

                    @if($errors->any())
                        <div class="alert de-card bg-danger bg-opacity-10 border-danger border-opacity-25 py-3 px-4 mb-4">
                            <ul class="mb-0 small ps-3 text-danger">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label x-small text-uppercase fw-black letter-spacing-1 opacity-50">Email Address</label>
                            <input type="email" name="email" class="form-control de-input" 
                                   value="{{ old('email') }}" required autofocus placeholder="name@email.com">
                        </div>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label text-white-50 small fw-bold text-uppercase tracking-wider">Password</label>
                                <a href="{{ route('password.request') }}" class="text-accent text-decoration-none x-small fw-bold">Forgot?</a>
                            </div>
                            <input type="password" name="password" class="form-control de-input" 
                                   required placeholder="••••••••">
                        </div>
                        
                        <div class="mb-4 d-flex align-items-center gap-2">
                            <input type="checkbox" class="form-check-input bg-transparent border-light border-opacity-10" name="remember" id="remember">
                            <label class="form-check-label small text-muted" for="remember">Keep me signed in</label>
                        </div>

                        <button type="submit" class="btn btn-de-gold w-100 py-3 mt-2">
                            Access Account <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </form>

                    <div class="text-center mt-5">
                        <p class="small text-muted">New to the platform? <a href="{{ route('register') }}" class="text-primary fw-bold text-decoration-none">Create Account</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

