@extends('layouts.app')
@section('title', 'Revenue - Admin')

@section('content')
<div class="container py-4">
    <h2 style="font-weight: 800;" class="mb-4"><i class="fas fa-chart-bar me-2" style="color: var(--de-primary);"></i>Platform Revenue</h2>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card"><div class="stat-value" style="color: var(--de-success);">LE {{ number_format($summary['total_revenue'], 2) }}</div><div class="stat-label">Platform Revenue</div></div>
        </div>
        <div class="col-md-3">
            <div class="stat-card"><div class="stat-value">LE {{ number_format($summary['restaurant_total'], 2) }}</div><div class="stat-label">Restaurant Payouts</div></div>
        </div>
        <div class="col-md-3">
            <div class="stat-card"><div class="stat-value">LE {{ number_format($summary['rider_total'], 2) }}</div><div class="stat-label">Rider Payouts</div></div>
        </div>
        <div class="col-md-3">
            <div class="stat-card"><div class="stat-value">{{ $summary['total_orders'] }}</div><div class="stat-label">Processed Orders</div></div>
        </div>
    </div>
    <div class="de-card">
        <div class="de-card-body">
            <h5 style="font-weight: 700;">Recent Payouts</h5>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead><tr><th>Order</th><th>Restaurant</th><th>Rider</th><th>Platform</th><th>Total</th><th>Rate</th><th>Date</th></tr></thead>
                    <tbody>
                    @foreach($payouts as $p)
                    <tr>
                        <td>#{{ $p->order_id }}</td>
                        <td class="text-success">LE {{ number_format($p->restaurant_amount, 2) }}</td>
                        <td class="text-primary">LE {{ number_format($p->rider_amount, 2) }}</td>
                        <td class="fw-bold">LE {{ number_format($p->platform_amount, 2) }}</td>
                        <td>LE {{ number_format($p->order_total, 2) }}</td>
                        <td>{{ $p->platform_commission_pct }}%</td>
                        <td class="small">{{ $p->created_at->format('M d') }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            {{ $payouts->links() }}
        </div>
    </div>
</div>
@endsection
