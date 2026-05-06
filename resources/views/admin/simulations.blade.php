@extends('layouts.app')
@section('title', 'System Simulations - DeliverEats')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5 animate-enter">
        <div>
            <h1 class="display-5 fw-black text-white">System Simulation Labs</h1>
            <p class="text-muted">Validate platform stability, state logic, and financial accuracy.</p>
        </div>
        <div class="d-flex flex-wrap gap-3">
            <form action="{{ route('admin.simulate.cleanup') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-danger border-opacity-25 py-2 px-4 rounded-pill small fw-bold">
                    <i class="fas fa-trash-alt me-2"></i> Reset System
                </button>
            </form>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-de-outline">
                <i class="fas fa-chevron-left me-2"></i> Back to Tower
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert de-card bg-success bg-opacity-10 border-success border-opacity-25 p-4 mb-5 animate-enter">
            <div class="d-flex align-items-center gap-3">
                <i class="fas fa-check-circle text-success fs-4"></i>
                <div class="text-success fw-bold">{{ session('success') }}</div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="alert de-card bg-danger bg-opacity-10 border-danger border-opacity-25 p-4 mb-5 animate-enter">
            <div class="d-flex align-items-center gap-3">
                <i class="fas fa-exclamation-triangle text-danger fs-4"></i>
                <div class="text-danger fw-bold">{{ session('error') }}</div>
            </div>
        </div>
    @endif

    <div class="row g-4">
        <!-- Order Volume Spike -->
        <div class="col-md-6">
            <div class="de-card h-100 animate-enter delay-100">
                <div class="de-card-body">
                    <div class="mb-4">
                        <div class="mb-3 d-inline-flex align-items-center justify-content-center" 
                             style="width: 60px; height: 60px; background: rgba(99, 102, 241, 0.1); border-radius: 18px;">
                            <i class="fas fa-bolt text-primary fs-3"></i>
                        </div>
                        <h3 class="text-white fw-bold">Volume Spike Simulation</h3>
                        <p class="text-muted small">Dispatches 50 concurrent orders to available riders to test real-time handling and assignment logic.</p>
                    </div>
                    <form action="{{ route('admin.simulate.spike') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-de w-100 py-3">
                            <i class="fas fa-rocket me-2"></i> Trigger 50 Orders
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- State Machine Validator -->
        <div class="col-md-6">
            <div class="de-card h-100 animate-enter delay-200">
                <div class="de-card-body">
                    <div class="mb-4">
                        <div class="mb-3 d-inline-flex align-items-center justify-content-center" 
                             style="width: 60px; height: 60px; background: rgba(239, 68, 68, 0.1); border-radius: 18px;">
                            <i class="fas fa-shield-alt text-danger fs-3"></i>
                        </div>
                        <h3 class="text-white fw-bold">State Machine Integrity</h3>
                        <p class="text-muted small">Validates that illegal transitions (e.g., jumping from Placed to Delivered) are correctly rejected by the system.</p>
                    </div>
                    <form action="{{ route('admin.simulate.state') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-de-outline w-100 py-3">
                            <i class="fas fa-vial me-2"></i> Run Logic Tests
                        </button>
                    </form>

                    @if(session('test_results'))
                    <div class="mt-4 pt-4 border-top border-light border-opacity-10">
                        @foreach(session('test_results') as $res)
                        <div class="d-flex justify-content-between align-items-center mb-2 small">
                            <span class="text-white">{{ $res['test'] }}</span>
                            <span class="badge {{ $res['status'] === 'PASS' ? 'bg-success' : 'bg-danger' }}">{{ $res['status'] }}</span>
                        </div>
                        <div class="x-small text-muted mb-3">{{ $res['message'] }}</div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Surge Pricing Test -->
        <div class="col-md-6">
            <div class="de-card h-100 animate-enter delay-300">
                <div class="de-card-body">
                    <div class="mb-4">
                        <div class="mb-3 d-inline-flex align-items-center justify-content-center" 
                             style="width: 60px; height: 60px; background: rgba(245, 158, 11, 0.1); border-radius: 18px;">
                            <i class="fas fa-chart-line text-warning fs-3"></i>
                        </div>
                        <h3 class="text-white fw-bold">Surge Pricing & Rollback</h3>
                        <p class="text-muted small">Tests the dynamic multiplier caps during demand spikes and verifies rollback to 1.0x when demand drops.</p>
                    </div>
                    <form action="{{ route('admin.simulate.surge') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-de-outline w-100 py-3">
                            <i class="fas fa-sync me-2"></i> Test Surge Logic
                        </button>
                    </form>

                    @if(session('surge_results'))
                    <div class="mt-4 pt-4 border-top border-light border-opacity-10">
                        <div class="row text-center g-3">
                            <div class="col-4">
                                <div class="x-small text-muted">Normal</div>
                                <div class="fw-black text-white">{{ session('surge_results')['normal'] }}x</div>
                            </div>
                            <div class="col-4">
                                <div class="x-small text-muted">Peak</div>
                                <div class="fw-black text-primary">{{ session('surge_results')['spike'] }}x</div>
                            </div>
                            <div class="col-4">
                                <div class="x-small text-muted">Rollback</div>
                                <div class="fw-black text-success">{{ session('surge_results')['rollback'] }}x</div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Payment Splits Accuracy -->
        <div class="col-md-6">
            <div class="de-card h-100 animate-enter delay-400">
                <div class="de-card-body">
                    <div class="mb-4">
                        <div class="mb-3 d-inline-flex align-items-center justify-content-center" 
                             style="width: 60px; height: 60px; background: rgba(16, 185, 129, 0.1); border-radius: 18px;">
                            <i class="fas fa-calculator text-success fs-3"></i>
                        </div>
                        <h3 class="text-white fw-bold">Payment Split Accuracy</h3>
                        <p class="text-muted small">Verifies payout calculations across different restaurant commission rates (Platform vs Restaurant vs Rider).</p>
                    </div>
                    <form action="{{ route('admin.simulate.payment') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-de-gold w-100 py-3">
                            <i class="fas fa-file-invoice-dollar me-2"></i> Verify Splits
                        </button>
                    </form>

                    @if(session('payment_results'))
                    <div class="mt-4 pt-4 border-top border-light border-opacity-10">
                        @foreach(session('payment_results') as $res)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small fw-bold text-white">{{ $res['restaurant'] }} ({{ $res['commission'] }})</span>
                                <span class="x-small text-muted">Total: LE {{ $res['total'] }}</span>
                            </div>
                            <div class="progress" style="height: 6px; background: rgba(255,255,255,0.05);">
                                <div class="progress-bar bg-primary" style="width: {{ ($res['platform_gets'] / $res['total']) * 100 }}%"></div>
                                <div class="progress-bar bg-accent" style="width: {{ ($res['restaurant_gets'] / $res['total']) * 100 }}%"></div>
                                <div class="progress-bar bg-success" style="width: {{ ($res['rider_gets'] / $res['total']) * 100 }}%"></div>
                            </div>
                            <div class="d-flex gap-3 x-small mt-2 text-muted">
                                <span>Platform: LE {{ $res['platform_gets'] }}</span>
                                <span>Shop: LE {{ $res['restaurant_gets'] }}</span>
                                <span>Rider: LE {{ $res['rider_gets'] }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
