@extends('layouts.app')
@section('title', 'Revenue - ' . $restaurant->name)

@section('styles')
<style>
    .stat-card-custom {
        background: var(--de-bg-navy);
        border: 1px solid var(--de-border);
        border-radius: 20px;
        padding: 1.5rem;
        transition: transform 0.3s ease;
    }
    .stat-card-custom:hover { transform: translateY(-5px); border-color: rgba(255,255,255,0.15); }
    .stat-value { font-size: 1.8rem; font-weight: 900; color: #fff; line-height: 1; margin-bottom: 0.5rem; }
    .stat-label { font-size: 0.75rem; font-weight: 700; color: var(--de-text-muted); text-transform: uppercase; letter-spacing: 0.05em; }
</style>
@endsection

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-end mb-5">
        <div>
            <h1 class="display-6 fw-bold text-white mb-1">Revenue Overview</h1>
            <p class="text-muted mb-0">Financial metrics for {{ $restaurant->name }}</p>
        </div>
        <div class="dropdown">
            <button class="btn btn-de-outline dropdown-toggle" type="button" data-bs-toggle="dropdown">
                {{ $period === '30days' ? 'Last 30 Days' : ($period === '7days' ? 'Last 7 Days' : 'Today') }}
            </button>
            <ul class="dropdown-menu dropdown-menu-dark">
                <li><a class="dropdown-item" href="?period=today">Today</a></li>
                <li><a class="dropdown-item" href="?period=7days">Last 7 Days</a></li>
                <li><a class="dropdown-item" href="?period=30days">Last 30 Days</a></li>
            </ul>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="stat-card-custom">
                <div class="stat-value text-primary">{{ $revenue['total_orders'] }}</div>
                <div class="stat-label">Total Orders</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-custom">
                <div class="stat-value text-accent">LE {{ number_format($revenue['restaurant_earnings'], 0) }}</div>
                <div class="stat-label">Net Earnings</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-custom">
                <div class="stat-value text-white">LE {{ number_format($revenue['total_revenue'], 0) }}</div>
                <div class="stat-label">Gross Revenue</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-custom">
                <div class="stat-value text-white">{{ number_format($revenue['avg_order_value'], 0) }}</div>
                <div class="stat-label">Avg Order (LE)</div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-lg-8">
            <div class="de-card h-100">
                <div class="de-card-body">
                    <h5 class="fw-bold text-white mb-4">Earnings History</h5>
                    <div style="height: 300px;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="de-card h-100">
                <div class="de-card-body">
                    <h5 class="fw-bold text-white mb-4">Recent Payouts</h5>
                    <div class="d-flex flex-column gap-3">
                        @foreach($recentPayouts->take(5) as $p)
                        <div class="p-3 rounded-4 bg-black bg-opacity-10 d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-white small fw-bold">Order #{{ $p->order_id }}</div>
                                <div class="text-muted x-small">{{ $p->created_at->format('M d, H:i') }}</div>
                            </div>
                            <div class="text-accent fw-black">LE {{ number_format($p->restaurant_amount, 0) }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
new Chart(document.getElementById('revenueChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_column($chartData, 'date')) !!},
        datasets: [{ label: 'Earnings (LE)', data: {!! json_encode(array_column($chartData, 'amount')) !!}, backgroundColor: 'rgba(255,107,53,0.7)', borderRadius: 8 }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
</script>
@endsection
