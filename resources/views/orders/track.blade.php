@extends('layouts.app')
@section('title', 'Track Order #' . $order->id)

@section('styles')
<style>
    .track-step { display: flex; align-items: flex-start; gap: 1.5rem; padding: 1.2rem 0; position: relative; }
    .track-dot { 
        width: 40px; height: 40px; border-radius: 50%; 
        display: flex; align-items: center; justify-content: center; 
        font-size: 0.9rem; color: #fff; flex-shrink: 0; z-index: 2;
        background: var(--de-bg-navy-light);
        border: 2px solid rgba(255,255,255,0.05);
        transition: all 0.3s ease;
    }
    .track-dot.active { background: var(--de-primary); box-shadow: 0 0 20px rgba(59,130,246,0.4); border-color: #fff; }
    .track-dot.done { background: var(--de-success); color: #fff; }
    .track-dot.pending { opacity: 0.5; }
    .track-line { 
        position: absolute; left: 19px; top: 52px; 
        width: 2px; height: calc(100% - 24px); 
        background: rgba(255,255,255,0.05); 
        z-index: 1;
    }
    .track-line.done { background: var(--de-success); opacity: 0.5; }
    #orderMap { height: 350px; border-radius: 20px; overflow: hidden; }
</style>
@endsection

@section('content')
<div class="container py-4">
    <div class="row">
        <!-- Order Info -->
        <div class="col-lg-5">
            <div class="de-card mb-3">
                <div class="de-card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="fw-bold text-white mb-0">Order #{{ $order->id }}</h4>
                        <span class="badge-status badge-{{ $order->status }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                    </div>
                    <p class="text-muted small mb-2"><i class="fas fa-store me-1"></i>{{ $order->restaurant->name }}</p>
                    <p class="text-muted small mb-2"><i class="fas fa-map-marker-alt me-1"></i>{{ $order->delivery_address }}</p>
                    @if($order->rider)
                    <p class="text-muted small mb-2"><i class="fas fa-motorcycle me-1"></i>Rider: {{ $order->rider->user->name }}</p>
                    @endif

                    <hr>
                    @foreach($order->items as $item)
                    <div class="d-flex justify-content-between small py-1">
                        <span>{{ $item->menuItem->name }} x{{ $item->quantity }}</span>
                        <span class="fw-bold">LE {{ number_format($item->subtotal, 2) }}</span>
                    </div>
                    @endforeach
                    <hr>
                    <div class="d-flex justify-content-between small"><span>Subtotal</span><span>LE {{ number_format($order->subtotal, 2) }}</span></div>
                    <div class="d-flex justify-content-between small"><span>Delivery</span><span>LE {{ number_format($order->delivery_fee, 2) }}</span></div>
                    @if($order->surge_fee > 0)
                    <div class="d-flex justify-content-between small text-warning"><span>Surge ({{ $order->surge_multiplier }}x)</span><span>LE {{ number_format($order->surge_fee, 2) }}</span></div>
                    @endif
                    <div class="d-flex justify-content-between small"><span>Tax</span><span>LE {{ number_format($order->tax, 2) }}</span></div>
                    @if($order->tip > 0)
                    <div class="d-flex justify-content-between small"><span>Tip</span><span>LE {{ number_format($order->tip, 2) }}</span></div>
                    @endif
                    <hr>
                    <div class="d-flex justify-content-between fw-bold"><span>Total</span><span style="color: var(--de-primary);">LE {{ number_format($order->total, 2) }}</span></div>
                </div>
            </div>

            <!-- Actions for restaurant/rider -->
            @auth
            @if(!$order->isTerminal() && count($allowedTransitions) > 0 && auth()->user()->role !== 'customer')
            <div class="de-card mb-3">
                <div class="de-card-body">
                    <h5 style="font-weight: 700;">Update Status</h5>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($allowedTransitions as $transition)
                        <form action="{{ route('orders.updateStatus', $order) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="{{ $transition }}">
                            <button type="submit" class="btn {{ $transition === 'cancelled' || $transition === 'rejected' ? 'btn-danger' : 'btn-de' }} btn-sm">
                                {{ ucfirst(str_replace('_', ' ', $transition)) }}
                            </button>
                        </form>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Rating (after delivery) -->
            @if($order->status === 'delivered' && $order->customer_id === auth()->id())
            <div class="de-card mb-3 border-accent border-opacity-25" id="rating-section">
                <div class="de-card-body">
                    <h5 class="fw-bold text-white mb-4"><i class="fas fa-star text-accent me-2"></i>Rate Your Experience</h5>
                    <form method="POST" action="{{ route('ratings.store', $order) }}">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label">Restaurant Rating</label>
                            <div class="d-flex gap-2">
                                @for($i=5; $i>=1; $i--)
                                <input type="radio" class="btn-check" name="restaurant_score" id="res-star-{{ $i }}" value="{{ $i }}" required>
                                <label class="btn btn-de-outline py-2 flex-grow-1" for="res-star-{{ $i }}">{{ $i }} <i class="fas fa-star small"></i></label>
                                @endfor
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Share your feedback</label>
                            <textarea name="restaurant_comment" class="form-control de-input" rows="3" placeholder="How was the food? Any special mentions?"></textarea>
                        </div>
                        @if($order->rider)
                        <div class="mb-4">
                            <label class="form-label">Rider Rating</label>
                            <div class="d-flex gap-2">
                                @for($i=5; $i>=1; $i--)
                                <input type="radio" class="btn-check" name="rider_score" id="rider-star-{{ $i }}" value="{{ $i }}">
                                <label class="btn btn-de-outline py-2 flex-grow-1" for="rider-star-{{ $i }}">{{ $i }} <i class="fas fa-star small"></i></label>
                                @endfor
                            </div>
                        </div>
                        @endif
                        <button type="submit" class="btn-de w-100 py-3 mt-2">
                            <i class="fas fa-paper-plane me-2"></i>Submit Review
                        </button>
                    </form>
                </div>
            </div>
            @endif
            @endauth
        </div>

        <!-- Tracking Timeline & Map -->
        <div class="col-lg-7">
            <div class="de-card mb-3">
                <div class="de-card-body">
                    <h5 style="font-weight: 700;" class="mb-3"><i class="fas fa-route me-2" style="color: var(--de-primary);"></i>Order Timeline</h5>
                    @php
                        $steps = ['placed','confirmed','preparing','ready_for_pickup','on_the_way','delivered'];
                        $currentIdx = array_search($order->status, $steps);
                        if ($currentIdx === false) $currentIdx = -1;
                    @endphp
                    @foreach($steps as $idx => $step)
                    <div class="track-step">
                        <div class="track-dot {{ $idx < $currentIdx ? 'done' : ($idx == $currentIdx ? 'active' : 'pending') }}">
                            @if($idx < $currentIdx) <i class="fas fa-check"></i>
                            @elseif($idx == $currentIdx) <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                            @else <span class="small">{{ $idx + 1 }}</span> @endif
                        </div>
                        <div>
                            <strong class="small">{{ ucfirst(str_replace('_', ' ', $step)) }}</strong>
                            @php $log = $order->stateLogs->firstWhere('to_state', $step); @endphp
                            @if($log)
                                <div class="text-muted" style="font-size: 0.7rem;">{{ $log->transitioned_at->format('M d, H:i:s') }} — by {{ $log->actor_type }}</div>
                            @endif
                        </div>
                        @if($idx < count($steps) - 1)
                        <div class="track-line {{ $idx < $currentIdx ? 'done' : '' }}"></div>
                        @endif
                    </div>
                    @endforeach

                    @if(in_array($order->status, ['cancelled', 'rejected']))
                    <div class="track-step">
                        <div class="track-dot" style="background: var(--de-danger);"><i class="fas fa-times"></i></div>
                        <div><strong class="small text-danger">{{ ucfirst($order->status) }}</strong></div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Map -->
            @if(!$order->isTerminal())
            <div class="de-card">
                <div class="de-card-body">
                    <h5 style="font-weight: 700;" class="mb-3"><i class="fas fa-map-marked-alt me-2" style="color: var(--de-primary);"></i>Live Tracking</h5>
                    <div id="orderMap"></div>
                    <p class="text-muted small mt-2 mb-0"><i class="fas fa-sync-alt me-1"></i>Auto-refreshing every 10 seconds</p>
                </div>
            </div>
            @endif

            <!-- State Log History -->
            <div class="de-card mt-3">
                <div class="de-card-body">
                    <h5 style="font-weight: 700;" class="mb-3"><i class="fas fa-history me-2"></i>Event Log</h5>
                    <div class="table-responsive">
                        <table class="table table-sm small">
                            <thead><tr><th>Time</th><th>Transition</th><th>Actor</th></tr></thead>
                            <tbody>
                            @foreach($order->stateLogs as $log)
                            <tr>
                                <td>{{ $log->transitioned_at->format('H:i:s') }}</td>
                                <td>{{ $log->formatted_transition }}</td>
                                <td><span class="badge bg-light text-dark">{{ $log->actor_type }}</span></td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
@if(!$order->isTerminal())
    // Initialize map
    const map = L.map('orderMap').setView([{{ $order->restaurant->lat ?: 30.0444 }}, {{ $order->restaurant->lng ?: 31.2357 }}], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);

    // Restaurant marker
    L.marker([{{ $order->restaurant->lat ?: 30.0444 }}, {{ $order->restaurant->lng ?: 31.2357 }}], {
        icon: L.divIcon({ html: '<i class="fas fa-store" style="color: #2563EB; font-size: 1.5rem;"></i>', className: '', iconSize: [30, 30] })
    }).addTo(map).bindPopup('{{ $order->restaurant->name }}');

    let riderMarker = null;

    function updateTracking() {
        fetch('{{ route("orders.status", $order) }}')
            .then(r => r.json())
            .then(data => {
                document.querySelector('.badge-status')?.setAttribute('class', 'badge-status badge-' + data.status);
                if (data.rider && data.rider.lat) {
                    if (riderMarker) map.removeLayer(riderMarker);
                    riderMarker = L.marker([data.rider.lat, data.rider.lng], {
                        icon: L.divIcon({ html: '<i class="fas fa-motorcycle" style="color: #1A1A2E; font-size: 1.5rem;"></i>', className: '', iconSize: [30, 30] })
                    }).addTo(map).bindPopup(data.rider.name);
                }
                if (data.status === 'delivered' || data.status === 'cancelled') location.reload();
            });
    }
    setInterval(updateTracking, 10000);
    updateTracking();
@endif
</script>
@endsection
