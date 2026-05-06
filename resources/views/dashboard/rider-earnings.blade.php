@extends('layouts.app')
@section('title', 'My Earnings')

@section('styles')
<style>
    .stat-card-custom {
        background: var(--de-bg-navy);
        border: 1px solid var(--de-border);
        border-radius: 20px;
        padding: 1.5rem;
    }
    .stat-value { font-size: 1.8rem; font-weight: 900; color: #fff; line-height: 1; margin-bottom: 0.5rem; }
    .stat-label { font-size: 0.75rem; font-weight: 700; color: var(--de-text-muted); text-transform: uppercase; letter-spacing: 0.05em; }
</style>
@endsection

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-end mb-5">
        <div>
            <h1 class="display-6 fw-bold text-white mb-1">My Wallet</h1>
            <p class="text-muted mb-0">Track your earnings and delivery performance</p>
        </div>
        <div class="dropdown">
            <button class="btn btn-de-outline dropdown-toggle" type="button" data-bs-toggle="dropdown">
                {{ $period === '30days' ? 'Last 30 Days' : ($period === '7days' ? 'Last 7 Days' : 'Today') }}
            </button>
            <ul class="dropdown-menu dropdown-menu-dark shadow-lg">
                <li><a class="dropdown-item" href="?period=today">Today</a></li>
                <li><a class="dropdown-item" href="?period=7days">Last 7 Days</a></li>
                <li><a class="dropdown-item" href="?period=30days">Last 30 Days</a></li>
            </ul>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="stat-card-custom border-success border-opacity-10">
                <div class="stat-value text-success">LE {{ number_format($earnings['total_earnings'], 0) }}</div>
                <div class="stat-label">Total Earned</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card-custom">
                <div class="stat-value text-white">{{ $earnings['total_deliveries'] }}</div>
                <div class="stat-label">Deliveries</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card-custom">
                <div class="stat-value text-primary">LE {{ number_format($earnings['avg_per_delivery'], 0) }}</div>
                <div class="stat-label">Avg. Per Trip</div>
            </div>
        </div>
    </div>

    <div class="de-card">
        <div class="de-card-body">
            <h5 class="fw-bold text-white mb-4">Recent Payouts</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="border-color: rgba(255,255,255,0.05);">
                    <thead class="bg-black bg-opacity-10">
                        <tr>
                            <th class="py-3 px-4" style="color: #475569;">Order</th>
                            <th class="py-3" style="color: #475569;">Restaurant</th>
                            <th class="py-3 text-end" style="color: #475569;">Earning</th>
                            <th class="py-3 px-4 text-end" style="color: #475569;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentPayouts as $p)
                        <tr>
                            <td class="px-4"><strong class="text-white small">#{{ $p->order_id }}</strong></td>
                            <td><span class="text-muted small">{{ $p->order->restaurant->name ?? 'N/A' }}</span></td>
                            <td class="text-end"><span class="text-success fw-bold">LE {{ number_format($p->rider_amount, 2) }}</span></td>
                            <td class="px-4 text-end text-muted x-small">{{ $p->created_at->format('M d, H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($recentPayouts->isEmpty())
                <div class="text-center py-5 text-muted small">No payouts recorded yet.</div>
            @endif
        </div>
    </div>
</div>
@endsection
