@extends('layouts.app')
@section('title', 'Admin Dashboard - DeliverEats')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 style="font-weight: 800;"><i class="fas fa-shield-alt me-2" style="color: var(--de-primary);"></i>Admin Control Tower</h2>
            <p class="text-muted mb-0">Real-time platform overview</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.liveMap') }}" class="btn-de"><i class="fas fa-map me-1"></i>Map</a>
            <a href="{{ route('admin.simulations') }}" class="btn-de-outline"><i class="fas fa-flask me-1 text-primary"></i>Simulations</a>
            <a href="{{ route('admin.orders') }}" class="btn-de-outline"><i class="fas fa-list me-1"></i>Orders</a>
            <a href="{{ route('admin.feedbacks') }}" class="btn-de-outline"><i class="fas fa-comment-dots me-1 text-accent"></i>Feedbacks</a>
            <a href="{{ route('admin.reviews') }}" class="btn-de-outline"><i class="fas fa-star me-1 text-accent"></i>Reviews</a>
            <a href="{{ route('admin.staff.index') }}" class="btn-de-gold"><i class="fas fa-users-cog me-1"></i>Staff</a>
        </div>
    </div>

    @livewire('admin.admin-control-tower')
</div>
@endsection
