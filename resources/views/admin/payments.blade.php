@extends('layouts.app')
@section('title', 'Payment Monitoring - DeliverEats Admin')

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-white mb-0"><i class="fas fa-file-invoice-dollar me-2 text-primary"></i>Payment Monitoring</h2>
    </div>

    <!-- Filters -->
    <div class="de-card mb-4">
        <div class="de-card-body py-3">
            <form action="{{ route('admin.payments') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-md-3">
                    <label class="small text-muted mb-1">Customer</label>
                    <select name="customer_id" class="form-select de-input">
                        <option value="">All Customers</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small text-muted mb-1">Status</label>
                    <select name="status" class="form-select de-input">
                        <option value="">All Statuses</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small text-muted mb-1">Method</label>
                    <select name="method" class="form-select de-input">
                        <option value="">All Methods</option>
                        <option value="stripe" {{ request('method') == 'stripe' ? 'selected' : '' }}>Stripe</option>
                        <option value="wallet" {{ request('method') == 'wallet' ? 'selected' : '' }}>Wallet</option>
                        <option value="cash" {{ request('method') == 'cash' ? 'selected' : '' }}>Cash</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn-de w-100 py-2">Filter</button>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <a href="{{ route('admin.payments') }}" class="btn-de-outline w-100 py-2">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="de-card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Method</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Order</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        <tr>
                            <td><span class="text-white small">#{{ $tx->id }}</span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 0.7rem;">
                                        {{ strtoupper(substr($tx->user->name, 0, 1)) }}
                                    </div>
                                    <span class="text-white small">{{ $tx->user->name }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $tx->type === 'deposit' ? 'bg-success' : 'bg-info' }} bg-opacity-10 text-{{ $tx->type === 'deposit' ? 'success' : 'info' }} small px-2">
                                    {{ ucfirst($tx->type) }}
                                </span>
                            </td>
                            <td><span class="text-muted small">{{ ucfirst($tx->method) }}</span></td>
                            <td><span class="text-white fw-bold">LE {{ number_format($tx->amount, 2) }}</span></td>
                            <td>
                                <span class="badge {{ $tx->status === 'completed' ? 'bg-success' : ($tx->status === 'pending' ? 'bg-warning' : 'bg-danger') }} bg-opacity-10 text-{{ $tx->status === 'completed' ? 'success' : ($tx->status === 'pending' ? 'warning' : 'danger') }} small px-2">
                                    {{ ucfirst($tx->status) }}
                                </span>
                            </td>
                            <td>
                                @if($tx->order_id)
                                    <a href="{{ route('orders.track', $tx->order_id) }}" class="text-primary small">Order #{{ $tx->order_id }}</a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td><span class="text-muted small">{{ $tx->created_at->format('M d, H:i') }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-search mb-3 d-block text-muted" style="font-size: 2rem;"></i>
                                <span class="text-muted">No transactions found matching your criteria.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-top border-light border-opacity-10">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection
