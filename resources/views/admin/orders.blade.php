@extends('layouts.app')
@section('title', 'All Orders - Admin')

@section('content')
<div class="container py-4">
    <h2 style="font-weight: 800;" class="mb-4">All Orders</h2>
    <div class="de-card mb-3">
        <div class="de-card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <select name="status" class="form-select de-input">
                        <option value="">All Statuses</option>
                        @foreach(['pending_payment','placed','confirmed','preparing','ready_for_pickup','on_the_way','delivered','cancelled','rejected'] as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3"><input type="date" name="date" class="form-control de-input" value="{{ request('date') }}"></div>
                <div class="col-md-2"><button type="submit" class="btn btn-de w-100">Filter</button></div>
            </form>
        </div>
    </div>
    <div class="de-card">
        <div class="de-card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" style="color: #94a3b8 !important; --bs-table-color: #94a3b8;">
                    <thead><tr><th style="color: #64748b;">#</th><th style="color: #64748b;">Restaurant</th><th style="color: #64748b;">Customer</th><th style="color: #64748b;">Rider</th><th style="color: #64748b;">Status</th><th style="color: #64748b;">Total</th><th style="color: #64748b;">Date</th><th></th></tr></thead>
                    <tbody>
                    @foreach($orders as $order)
                    <tr>
                        <td><strong>#{{ $order->id }}</strong></td>
                        <td>{{ $order->restaurant->name }}</td>
                        <td>{{ $order->customer->name }}</td>
                        <td>{{ $order->rider ? $order->rider->user->name : '—' }}</td>
                        <td><span class="badge-status badge-{{ $order->status }}">{{ ucfirst(str_replace('_',' ',$order->status)) }}</span></td>
                        <td>LE {{ number_format($order->total, 2) }}</td>
                        <td class="small">{{ $order->created_at->format('M d, H:i') }}</td>
                        <td><a href="{{ route('orders.track', $order) }}" class="btn btn-sm btn-de-outline">View</a></td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-3">{{ $orders->links() }}</div>
        </div>
    </div>
</div>
@endsection
