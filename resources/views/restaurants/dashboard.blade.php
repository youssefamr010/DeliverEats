@extends('layouts.app')
@section('title', 'Restaurant Dashboard - DeliverEats')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 style="font-weight: 800;">{{ $restaurant->name }}</h2>
            <p class="text-muted mb-0">Restaurant Dashboard</p>
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('restaurant.toggleOpen', $restaurant) }}" method="POST">
                @csrf
                <button type="submit" class="btn {{ $restaurant->is_open ? 'btn-success' : 'btn-secondary' }}">
                    <i class="fas fa-{{ $restaurant->is_open ? 'toggle-on' : 'toggle-off' }} me-1"></i>
                    {{ $restaurant->is_open ? 'Open' : 'Closed' }}
                </button>
            </form>
            <a href="{{ route('restaurant.edit', $restaurant) }}" class="btn btn-de-outline"><i class="fas fa-edit me-1"></i>Edit</a>
            <a href="{{ route('menu.categories', $restaurant) }}" class="btn btn-de"><i class="fas fa-list me-1"></i>Menu</a>
            <a href="{{ route('restaurant.staff.index') }}" class="btn btn-de-gold"><i class="fas fa-users me-1"></i>Staff</a>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background: var(--de-gradient);"><i class="fas fa-receipt"></i></div>
                    <div>
                        <div class="stat-value">{{ $todayOrders->count() }}</div>
                        <div class="stat-label">Today's Orders</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #00C853, #69F0AE);"><i class="fas fa-dollar-sign"></i></div>
                    <div>
                        <div class="stat-value">LE {{ number_format($revenue['restaurant_earnings'], 2) }}</div>
                        <div class="stat-label">Today's Earnings</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #2196F3, #64B5F6);"><i class="fas fa-hourglass-half"></i></div>
                    <div>
                        <div class="stat-value">{{ $pendingOrders->count() }}</div>
                        <div class="stat-label">Pending Orders</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #FFD600, #FFF176);"><i class="fas fa-star"></i></div>
                    <div>
                        <div class="stat-value">{{ number_format($restaurant->rating_avg, 1) }}</div>
                        <div class="stat-label">Rating ({{ $restaurant->rating_count }})</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Orders -->
    <div class="de-card mb-4">
        <div class="de-card-body">
            <h4 style="font-weight: 700; color: #94a3b8 !important;" class="mb-3"><i class="fas fa-clock text-warning me-2"></i>Active Orders</h4>
            @if($pendingOrders->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle" style="color: #94a3b8 !important; --bs-table-color: #94a3b8;">
                    <thead><tr><th style="color: #64748b;">#</th><th style="color: #64748b;">Customer</th><th style="color: #64748b;">Items</th><th style="color: #64748b;">Total</th><th style="color: #64748b;">Status</th><th style="color: #64748b;">Actions</th></tr></thead>
                    <tbody>
                    @foreach($pendingOrders as $order)
                    <tr>
                        <td><strong>#{{ $order->id }}</strong></td>
                        <td>{{ $order->customer->name }}</td>
                        <td>{{ $order->items->count() }} items</td>
                        <td><strong>LE {{ number_format($order->total, 2) }}</strong></td>
                        <td><span class="badge-status badge-{{ $order->status }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span></td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('orders.track', $order) }}" class="btn btn-sm btn-de-outline">View</a>
                                @php $transitions = \App\StateMachine\OrderStateMachine::getAllowedTransitions($order->status); @endphp
                                @foreach($transitions as $t)
                                    @if($t !== 'cancelled')
                                    <form action="{{ route('orders.updateStatus', $order) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="status" value="{{ $t }}">
                                        <button type="submit" class="btn btn-sm btn-de">{{ ucfirst(str_replace('_', ' ', $t)) }}</button>
                                    </form>
                                    @endif
                                @endforeach
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-muted text-center py-3"><i class="fas fa-check-circle me-1"></i>No pending orders</p>
            @endif
        </div>
    </div>

    <!-- Quick Links -->
    <div class="row g-3">
        <div class="col-md-4">
            <a href="{{ route('menu.categories', $restaurant) }}" class="text-decoration-none">
                <div class="stat-card text-center py-4">
                    <i class="fas fa-list-alt" style="font-size: 2rem; color: var(--de-primary);"></i>
                    <h5 class="mt-2" style="font-weight: 700;">Menu Management</h5>
                    <p class="text-muted small mb-0">{{ $restaurant->categories->count() }} categories</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('restaurant.revenue') }}" class="text-decoration-none">
                <div class="stat-card text-center py-4">
                    <i class="fas fa-chart-line" style="font-size: 2rem; color: var(--de-success);"></i>
                    <h5 class="mt-2" style="font-weight: 700;">Revenue Dashboard</h5>
                    <p class="text-muted small mb-0">View earnings & payouts</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('restaurants.reviews', $restaurant) }}" class="text-decoration-none">
                <div class="stat-card text-center py-4">
                    <i class="fas fa-star" style="font-size: 2rem; color: #FFD600;"></i>
                    <h5 class="mt-2" style="font-weight: 700;">Reviews</h5>
                    <p class="text-muted small mb-0">{{ $restaurant->rating_count }} reviews</p>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
