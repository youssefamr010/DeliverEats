@extends('layouts.app')
@section('title', 'My Orders - DeliverEats')

@section('content')
<div class="container py-4">
    <h2 style="font-weight: 800;" class="mb-4"><i class="fas fa-receipt me-2" style="color: var(--de-primary);"></i>My Orders</h2>

    @forelse($orders as $order)
    <div class="de-card mb-3">
        <div class="de-card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h5 style="font-weight: 700; margin-bottom: 0.25rem;">
                        Order #{{ $order->id }}
                        <span class="badge-status badge-{{ $order->status }} ms-2">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                    </h5>
                    <p class="text-muted small mb-1"><i class="fas fa-store me-1"></i>{{ $order->restaurant->name }}</p>
                    <p class="text-muted small mb-1"><i class="fas fa-calendar me-1"></i>{{ $order->created_at->format('M d, Y H:i') }}</p>
                    <p class="text-muted small mb-0">{{ $order->items->count() }} items</p>
                </div>
                <div class="text-end">
                    <div style="font-size: 1.3rem; font-weight: 800; color: var(--de-primary);">LE {{ number_format($order->total, 2) }}</div>
                    <a href="{{ route('orders.track', $order) }}" class="btn btn-de btn-sm mt-2">
                        <i class="fas fa-eye me-1"></i>{{ $order->isTerminal() ? 'View' : 'Track' }}
                    </a>
                    @if($order->status === 'delivered' && $order->ratings->where('user_id', auth()->id())->isEmpty())
                        <a href="{{ route('orders.track', $order) }}#rating-section" class="btn btn-de-gold btn-sm mt-2">
                            <i class="fas fa-star me-1"></i>Rate
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="text-center py-5">
        <i class="fas fa-receipt" style="font-size: 4rem; color: #DDD;"></i>
        <h4 class="mt-3" style="color: #888;">No orders yet</h4>
        <a href="{{ route('restaurants.index') }}" class="btn btn-de mt-2">Browse Restaurants</a>
    </div>
    @endforelse

    <div class="d-flex justify-content-center mt-3">{{ $orders->links() }}</div>
</div>
@endsection
