@extends('layouts.app')
@section('title', 'My Wallet - DeliverEats')

@section('content')
<div class="container py-4">
    <div class="row g-4">
        <!-- Wallet Card -->
        <div class="col-lg-4">
            <div class="de-card overflow-hidden animate-enter">
                <div class="de-card-body p-0">
                    <div class="bg-primary p-4 text-center">
                        <i class="fas fa-wallet text-white display-4 mb-3 opacity-50"></i>
                        <h6 class="text-white text-opacity-75 mb-1">Available Balance</h6>
                        <h2 class="text-white fw-black mb-0">LE {{ number_format($user->wallet_balance, 2) }}</h2>
                    </div>
                    <div class="p-4">
                        <h6 class="text-white fw-bold mb-3">Top Up Wallet</h6>
                        <form action="{{ route('wallet.topup') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="small text-muted mb-2">Amount (LE)</label>
                                <div class="input-group">
                                    <span class="input-group-text de-input bg-dark border-end-0">LE</span>
                                    <input type="number" name="amount" class="form-control de-input border-start-0" placeholder="Min. 50" min="50" required>
                                </div>
                            </div>
                            <button type="submit" class="btn-de w-100 py-3">
                                <i class="fab fa-stripe me-2"></i>Pay with Stripe
                            </button>
                        </form>
                        <p class="small text-muted mt-3 text-center">
                            <i class="fas fa-shield-alt me-1"></i>Secure payments via Stripe
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction History -->
        <div class="col-lg-8">
            <div class="de-card animate-enter" style="animation-delay: 0.1s;">
                <div class="de-card-body">
                    <h5 class="fw-bold text-white mb-4"><i class="fas fa-history text-primary me-2"></i>Transaction History</h5>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Method</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $tx)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fas {{ $tx->type === 'deposit' ? 'fa-arrow-down text-success' : 'fa-arrow-up text-danger' }} small"></i>
                                                <span class="text-white small">{{ ucfirst($tx->type) }}</span>
                                            </div>
                                        </td>
                                        <td><span class="text-muted small">{{ ucfirst($tx->method) }}</span></td>
                                        <td>
                                            <span class="fw-bold {{ $tx->type === 'deposit' ? 'text-success' : 'text-white' }} small">
                                                {{ $tx->type === 'deposit' ? '+' : '-' }} LE {{ number_format($tx->amount, 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $tx->status === 'completed' ? 'bg-success' : 'bg-warning' }} bg-opacity-10 text-{{ $tx->status === 'completed' ? 'success' : 'warning' }} small px-2">
                                                {{ ucfirst($tx->status) }}
                                            </span>
                                        </td>
                                        <td><span class="text-muted small">{{ $tx->created_at->format('M d, H:i') }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <p class="text-muted small mb-0">No transactions yet.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $transactions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
