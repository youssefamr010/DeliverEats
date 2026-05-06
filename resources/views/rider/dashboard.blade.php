@extends('layouts.app')
@section('title', 'Rider Dashboard - DeliverEats')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 style="font-weight: 800;"><i class="fas fa-motorcycle me-2" style="color: var(--de-gold);"></i>Rider Dashboard</h2>
            <p class="text-muted mb-0">Welcome, {{ auth()->user()->name }}</p>
        </div>
        <form action="{{ route('rider.toggleOnline') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-lg {{ $rider->is_online ? 'btn-success' : 'btn-secondary' }}">
                <i class="fas fa-{{ $rider->is_online ? 'toggle-on' : 'toggle-off' }} me-2"></i>
                {{ $rider->is_online ? 'Online' : 'Offline' }}
            </button>
        </form>
    </div>

    {{-- Profile Card --}}
    <div class="de-card mb-4">
        <div class="de-card-body">
            <div class="row align-items-center">
                <div class="col-md-2 text-center">
                    <div style="width: 80px; height: 80px; border-radius: 50%; background: var(--de-gold-gradient); display: inline-flex; align-items: center; justify-content: center; font-size: 2rem; color: #0B1120;">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
                <div class="col-md-5">
                    <h5 class="fw-bold mb-1">{{ auth()->user()->name }}</h5>
                    <p class="text-muted small mb-0"><i class="fas fa-envelope me-1"></i>{{ auth()->user()->email }}</p>
                    <p class="text-muted small mb-0"><i class="fas fa-phone me-1"></i>{{ auth()->user()->phone ?? 'No phone' }}</p>
                    <p class="text-muted small mb-0"><i class="fas fa-{{ $rider->vehicle_type ?? 'motorcycle' }} me-1"></i>{{ ucfirst($rider->vehicle_type ?? 'Motorcycle') }}</p>
                </div>
                <div class="col-md-5">
                    <div class="row text-center">
                        <div class="col-4">
                            <div style="font-size: 1.5rem; font-weight: 800; color: var(--de-gold);">{{ number_format($rider->rating_avg, 1) }}</div>
                            <div class="small text-muted">Rating</div>
                        </div>
                        <div class="col-4">
                            <div style="font-size: 1.5rem; font-weight: 800; color: var(--de-primary);">{{ $rider->total_deliveries }}</div>
                            <div class="small text-muted">Deliveries</div>
                        </div>
                        <div class="col-4">
                            <div style="font-size: 1.5rem; font-weight: 800; color: var(--de-success);">LE {{ number_format($allTimeEarnings, 2) }}</div>
                            <div class="small text-muted">All-Time</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Earnings Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background: var(--de-gradient);"><i class="fas fa-check-circle"></i></div>
                    <div><div class="stat-value">{{ $completedToday }}</div><div class="stat-label">Today</div></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #00C853, #69F0AE);"><i class="fas fa-dollar-sign"></i></div>
                    <div><div class="stat-value">LE {{ number_format($earnings['total_earnings'], 2) }}</div><div class="stat-label">Today Earnings</div></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background: var(--de-gold-gradient);"><i class="fas fa-hand-holding-usd" style="color: #0B1120;"></i></div>
                    <div><div class="stat-value">LE {{ number_format($earnings['tip_earnings'], 2) }}</div><div class="stat-label">Today Tips</div></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #2196F3, #64B5F6);"><i class="fas fa-wallet"></i></div>
                    <div><div class="stat-value">LE {{ number_format($monthlyEarnings['total_earnings'], 2) }}</div><div class="stat-label">This Month</div></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            {{-- Pending Dispatches --}}
            <div class="de-card mb-3">
                <div class="de-card-body">
                    <h5 style="font-weight: 700;"><i class="fas fa-bell text-warning me-2"></i>Incoming Orders</h5>
                    @forelse($pendingDispatches as $dispatch)
                    <div class="border rounded-3 p-3 mb-2" style="border-color: var(--de-gold) !important;">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>Order #{{ $dispatch->order->id }}</strong>
                                <p class="small text-muted mb-1">{{ $dispatch->order->restaurant->name }}</p>
                                <p class="small text-muted mb-0"><i class="fas fa-ruler me-1"></i>{{ $dispatch->distance_km }} km away</p>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold" style="color: var(--de-gold);">LE {{ number_format($dispatch->order->delivery_fee + $dispatch->order->tip, 2) }}</div>
                                <div class="d-flex gap-1 mt-2">
                                    <form action="{{ route('rider.acceptDispatch', $dispatch) }}" method="POST">@csrf
                                        <button class="btn btn-sm btn-success"><i class="fas fa-check me-1"></i>Accept</button>
                                    </form>
                                    <form action="{{ route('rider.rejectDispatch', $dispatch) }}" method="POST">@csrf
                                        <button class="btn btn-sm btn-danger"><i class="fas fa-times me-1"></i>Reject</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center py-3">No incoming orders. Stay online to receive dispatches!</p>
                    @endforelse
                </div>
            </div>

            {{-- Active Orders --}}
            <div class="de-card">
                <div class="de-card-body">
                    <h5 style="font-weight: 700;"><i class="fas fa-motorcycle me-2" style="color: var(--de-primary);"></i>Active Deliveries</h5>
                    @forelse($activeOrders as $order)
                    <div class="border rounded-3 p-3 mb-2">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>Order #{{ $order->id }}</strong>
                                <span class="badge-status badge-{{ $order->status }} ms-2">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                                <p class="small text-muted mb-1 mt-1">{{ $order->restaurant->name }}</p>
                                <p class="small text-muted mb-0"><i class="fas fa-map-marker-alt me-1"></i>{{ $order->delivery_address }}</p>
                            </div>
                            <a href="{{ route('orders.track', $order) }}" class="btn btn-sm btn-de">Manage</a>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center py-3">No active deliveries.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Map --}}
        <div class="col-lg-6">
            <div class="de-card">
                <div class="de-card-body">
                    <h5 style="font-weight: 700;"><i class="fas fa-map-marked-alt me-2" style="color: var(--de-gold);"></i>Your Location</h5>
                    <div id="riderMap" style="height: 400px; border-radius: 12px;"></div>
                    <p class="text-muted small mt-2 mb-0">
                        <i class="fas fa-crosshairs me-1"></i>
                        Lat: {{ $rider->current_lat ?? 'N/A' }}, Lng: {{ $rider->current_lng ?? 'N/A' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const lat = {{ $rider->current_lat ?? 30.0444 }};
const lng = {{ $rider->current_lng ?? 31.2357 }};
const map = L.map('riderMap').setView([lat, lng], 14);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);
L.marker([lat, lng], {
    icon: L.divIcon({ html: '<i class="fas fa-motorcycle" style="color: #D4A843; font-size: 2rem;"></i>', className: '', iconSize: [30, 30] })
}).addTo(map).bindPopup('Your Location');
</script>
@endsection
