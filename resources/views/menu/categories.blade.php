@extends('layouts.app')
@section('title', 'Menu Management')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 style="font-weight: 800;">Menu Categories</h2>
            <p class="text-muted mb-0">{{ $restaurant->name }}</p>
        </div>
        <a href="{{ route('restaurant.dashboard') }}" class="btn btn-de-outline"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>

    <!-- Add Category -->
    <div class="de-card mb-4">
        <div class="de-card-body">
            <h5 style="font-weight: 700;"><i class="fas fa-plus-circle me-2" style="color: var(--de-primary);"></i>Add Category</h5>
            <form method="POST" action="{{ route('menu.categories.store', $restaurant) }}" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-5"><input type="text" name="name" class="form-control de-input" placeholder="Category name" required></div>
                <div class="col-md-4"><input type="text" name="description" class="form-control de-input" placeholder="Description (optional)"></div>
                <div class="col-md-1"><input type="number" name="sort_order" class="form-control de-input" value="0" placeholder="#"></div>
                <div class="col-md-2"><button type="submit" class="btn btn-de w-100">Add</button></div>
            </form>
        </div>
    </div>

    <!-- Categories List -->
    @foreach($categories as $category)
    <div class="de-card mb-3">
        <div class="de-card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 style="font-weight: 700; margin: 0;">
                    {{ $category->name }}
                    <span class="badge {{ $category->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $category->is_active ? 'Active' : 'Inactive' }}</span>
                    <span class="text-muted small ms-2">({{ $category->menuItems->count() }} items)</span>
                </h5>
                <div class="d-flex gap-1">
                    <a href="{{ route('menu.items', $category) }}" class="btn btn-sm btn-de"><i class="fas fa-utensils me-1"></i>Items</a>
                    <form action="{{ route('menu.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Delete this category?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>
            @if($category->description)<p class="text-muted small mb-0">{{ $category->description }}</p>@endif
        </div>
    </div>
    @endforeach
</div>
@endsection
