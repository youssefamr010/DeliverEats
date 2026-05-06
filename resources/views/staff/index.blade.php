@extends('layouts.app')
@section('title', 'Manage Staff - DeliverEats')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 style="font-weight: 800;"><i class="fas fa-users-cog me-2" style="color: var(--de-gold);"></i>Staff Management</h2>
            <p class="text-muted mb-0">Add, edit, and manage team members</p>
        </div>
        <a href="{{ auth()->user()->role === 'admin' ? route('admin.staff.create') : route('restaurant.staff.create') }}" class="btn btn-de">
            <i class="fas fa-plus me-1"></i>Add Staff
        </a>
    </div>

    @if(auth()->user()->role === 'admin')
    <div class="de-card mb-3">
        <div class="de-card-body py-2">
            <form method="GET" class="d-flex gap-2 align-items-center flex-wrap">
                <select name="role" class="form-select de-input" style="width: auto;">
                    <option value="">All Roles</option>
                    <option value="chef" {{ request('role') == 'chef' ? 'selected' : '' }}>Chefs</option>
                    <option value="rider" {{ request('role') == 'rider' ? 'selected' : '' }}>Riders</option>
                    <option value="restaurant_owner" {{ request('role') == 'restaurant_owner' ? 'selected' : '' }}>Restaurant Owners</option>
                    <option value="customer" {{ request('role') == 'customer' ? 'selected' : '' }}>Customers</option>
                </select>
                @if($restaurants->count())
                <select name="restaurant_id" class="form-select de-input" style="width: auto;">
                    <option value="">All Restaurants</option>
                    @foreach($restaurants as $r)
                        <option value="{{ $r->id }}" {{ request('restaurant_id') == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                    @endforeach
                </select>
                @endif
                <button class="btn btn-de btn-sm">Filter</button>
                <a href="{{ route('admin.staff.index') }}" class="btn btn-de-outline btn-sm">Reset</a>
            </form>
        </div>
    </div>
    @endif

    <div class="de-card">
        <div class="de-card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Restaurant</th><th>Phone</th><th>Actions</th></tr></thead>
                    <tbody>
                    @forelse($staff as $member)
                    <tr>
                        <td><strong>{{ $member->name }}</strong></td>
                        <td class="small">{{ $member->email }}</td>
                        <td>
                            @php
                                $roleColors = ['admin' => 'danger', 'restaurant_owner' => 'primary', 'chef' => 'warning', 'rider' => 'info', 'customer' => 'secondary'];
                            @endphp
                            <span class="badge bg-{{ $roleColors[$member->role] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $member->role)) }}</span>
                        </td>
                        <td class="small">{{ $member->assignedRestaurant->name ?? ($member->ownedRestaurant->name ?? '—') }}</td>
                        <td class="small">{{ $member->phone ?? '—' }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ auth()->user()->role === 'admin' ? route('admin.staff.edit', $member) : route('restaurant.staff.edit', $member) }}" class="btn btn-sm btn-de-outline"><i class="fas fa-edit"></i></a>
                                @if($member->id !== auth()->id())
                                <form action="{{ auth()->user()->role === 'admin' ? route('admin.staff.destroy', $member) : route('restaurant.staff.destroy', $member) }}" method="POST" onsubmit="return confirm('Delete {{ $member->name }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No staff members found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $staff->appends(request()->query())->links() }}</div>
        </div>
    </div>
</div>
@endsection
