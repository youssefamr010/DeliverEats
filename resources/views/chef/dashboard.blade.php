@extends('layouts.app')
@section('title', 'Kitchen Dashboard - DeliverEats')

@section('styles')
<style>
    .kitchen-col { min-height: 400px; }
    .order-ticket {
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        border-left: 4px solid;
        transition: all 0.3s;
    }
    .order-ticket:hover { transform: translateX(4px); }
    .ticket-incoming { border-left-color: var(--de-warning); background: rgba(255,214,0,0.05); }
    .ticket-preparing { border-left-color: var(--de-primary); background: rgba(37,99,235,0.05); }
    .ticket-ready { border-left-color: var(--de-success); background: rgba(0,200,83,0.05); }
    .kitchen-header {
        font-weight: 800;
        font-size: 1rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 style="font-weight: 800;"><i class="fas fa-fire-burner me-2" style="color: var(--de-gold);"></i>Kitchen — {{ $restaurant->name }}</h2>
            <p class="text-muted mb-0">
                <i class="fas fa-check-circle text-success me-1"></i>{{ $completedToday }} orders completed today
            </p>
        </div>
        <span class="badge bg-success pulse px-3 py-2" style="font-size: 0.85rem;">● Kitchen Live</span>
    </div>

    <div class="row g-4">
        {{-- INCOMING ORDERS --}}
        <div class="col-lg-4 kitchen-col">
            <div class="kitchen-header" style="border-color: var(--de-warning); color: var(--de-warning);">
                <i class="fas fa-bell me-1"></i> Incoming ({{ $incomingOrders->count() }})
            </div>
            @forelse($incomingOrders as $order)
            <div class="order-ticket ticket-incoming de-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong>#{{ $order->id }}</strong>
                        <span class="badge-status badge-{{ $order->status }} ms-1">{{ ucfirst($order->status) }}</span>
                        <p class="small text-muted mb-1 mt-1">{{ $order->customer->name }}</p>
                    </div>
                    <span class="small text-muted">{{ $order->created_at->diffForHumans() }}</span>
                </div>
                <div class="small mb-2">
                    @foreach($order->items as $item)
                        <div><i class="fas fa-circle" style="font-size: 0.4rem; vertical-align: middle;"></i> {{ $item->menuItem->name }} x{{ $item->quantity }}</div>
                    @endforeach
                </div>
                @if($order->notes)<div class="small text-info mb-2"><i class="fas fa-sticky-note me-1"></i>{{ $order->notes }}</div>@endif
                <div class="d-flex gap-1">
                    @if($order->status === 'placed')
                    <form action="{{ route('orders.updateStatus', $order) }}" method="POST">@csrf
                        <input type="hidden" name="status" value="confirmed">
                        <button class="btn btn-sm btn-de"><i class="fas fa-check me-1"></i>Confirm</button>
                    </form>
                    @endif
                    @if($order->status === 'confirmed')
                    <form action="{{ route('orders.updateStatus', $order) }}" method="POST">@csrf
                        <input type="hidden" name="status" value="preparing">
                        <button class="btn btn-sm btn-warning text-dark"><i class="fas fa-fire me-1"></i>Start Preparing</button>
                    </form>
                    @endif
                    <form action="{{ route('orders.updateStatus', $order) }}" method="POST">@csrf
                        <input type="hidden" name="status" value="cancelled">
                        <button class="btn btn-sm btn-danger"><i class="fas fa-times"></i></button>
                    </form>
                </div>
            </div>
            @empty
            <p class="text-muted text-center py-5"><i class="fas fa-inbox" style="font-size: 2rem;"></i><br>No incoming orders</p>
            @endforelse
        </div>

        {{-- PREPARING --}}
        <div class="col-lg-4 kitchen-col">
            <div class="kitchen-header" style="border-color: var(--de-primary); color: var(--de-primary);">
                <i class="fas fa-fire me-1"></i> Preparing ({{ $preparingOrders->count() }})
            </div>
            @forelse($preparingOrders as $order)
            <div class="order-ticket ticket-preparing de-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong>#{{ $order->id }}</strong>
                        <span class="badge-status badge-preparing ms-1">Preparing</span>
                    </div>
                    <span class="small text-muted">{{ $order->created_at->diffForHumans() }}</span>
                </div>
                <div class="small mb-2">
                    @foreach($order->items as $item)
                        <div><i class="fas fa-circle" style="font-size: 0.4rem; vertical-align: middle;"></i> {{ $item->menuItem->name }} x{{ $item->quantity }}</div>
                    @endforeach
                </div>
                <div class="d-flex gap-1">
                    <form action="{{ route('orders.updateStatus', $order) }}" method="POST">@csrf
                        <input type="hidden" name="status" value="ready_for_pickup">
                        <button class="btn btn-sm btn-success"><i class="fas fa-check-double me-1"></i>Ready!</button>
                    </form>
                    <form action="{{ route('orders.updateStatus', $order) }}" method="POST">@csrf
                        <input type="hidden" name="status" value="cancelled">
                        <button class="btn btn-sm btn-danger"><i class="fas fa-times me-1"></i>Cancel</button>
                    </form>
                </div>
            </div>
            @empty
            <p class="text-muted text-center py-5"><i class="fas fa-pause-circle" style="font-size: 2rem;"></i><br>Nothing in the kitchen</p>
            @endforelse
        </div>

        {{-- READY FOR PICKUP --}}
        <div class="col-lg-4 kitchen-col">
            <div class="kitchen-header" style="border-color: var(--de-success); color: var(--de-success);">
                <i class="fas fa-box me-1"></i> Ready ({{ $readyOrders->count() }})
            </div>
            @forelse($readyOrders as $order)
            <div class="order-ticket ticket-ready de-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong>#{{ $order->id }}</strong>
                        <span class="badge-status badge-ready_for_pickup ms-1">Ready</span>
                    </div>
                </div>
                <div class="small mb-1">
                    @foreach($order->items as $item)
                        <div><i class="fas fa-circle" style="font-size: 0.4rem; vertical-align: middle;"></i> {{ $item->menuItem->name }} x{{ $item->quantity }}</div>
                    @endforeach
                </div>
                @if($order->rider)
                <div class="small text-success mt-1"><i class="fas fa-motorcycle me-1"></i>Rider: {{ $order->rider->user->name }}</div>
                @else
                <div class="small text-warning mt-1"><i class="fas fa-hourglass-half me-1"></i>Waiting for rider...</div>
                @endif
            </div>
            @empty
            <p class="text-muted text-center py-5"><i class="fas fa-box-open" style="font-size: 2rem;"></i><br>No orders ready</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
