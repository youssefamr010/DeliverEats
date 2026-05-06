@extends('layouts.app')
@section('title', 'Menu Items - ' . $category->name)

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 style="font-weight: 800;">{{ $category->name }} — Items</h2>
            <p class="text-muted mb-0">{{ $category->restaurant->name }}</p>
        </div>
        <a href="{{ route('menu.categories', $category->restaurant) }}" class="btn btn-de-outline"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>

    <!-- Add Item -->
    <div class="de-card mb-4">
        <div class="de-card-body">
            <h5 style="font-weight: 700;"><i class="fas fa-plus-circle me-2" style="color: var(--de-primary);"></i>Add Item</h5>
            <form method="POST" action="{{ route('menu.items.store', $category) }}" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-3"><input type="text" name="name" class="form-control de-input" placeholder="Item name" required></div>
                <div class="col-md-3"><input type="text" name="description" class="form-control de-input" placeholder="Description"></div>
                <div class="col-md-2"><input type="number" step="0.01" name="base_price" class="form-control de-input" placeholder="Price" required></div>
                <div class="col-md-2"><input type="number" name="prep_time" class="form-control de-input" placeholder="Prep (min)"></div>
                <div class="col-md-2"><button type="submit" class="btn btn-de w-100">Add</button></div>
            </form>
        </div>
    </div>

    <!-- Items List -->
    @foreach($category->menuItems as $item)
    <div class="de-card mb-3">
        <div class="de-card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h5 style="font-weight: 700; margin-bottom: 0.25rem;">
                        {{ $item->name }}
                        <span class="badge {{ $item->is_available ? 'bg-success' : 'bg-secondary' }}">{{ $item->is_available ? 'Available' : 'Unavailable' }}</span>
                    </h5>
                    @if($item->description)<p class="text-muted small mb-1">{{ $item->description }}</p>@endif
                    <span style="font-weight: 700; color: var(--de-primary); font-size: 1.1rem;">LE {{ number_format($item->base_price, 2) }}</span>
                    @if($item->prep_time)<span class="text-muted small ms-2"><i class="fas fa-clock"></i> {{ $item->prep_time }}min</span>@endif
                </div>
                <div class="d-flex gap-1">
                    <form action="{{ route('menu.items.toggle', $item) }}" method="POST">@csrf
                        <button class="btn btn-sm {{ $item->is_available ? 'btn-warning' : 'btn-success' }}">
                            {{ $item->is_available ? 'Disable' : 'Enable' }}
                        </button>
                    </form>
                    <form action="{{ route('menu.items.destroy', $item) }}" method="POST" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>

            <!-- Variants -->
            <div class="mt-3">
                <h6 class="small fw-bold text-muted">VARIANTS</h6>
                @foreach($item->variants as $variant)
                <div class="d-inline-flex align-items-center gap-2 me-3 mb-2">
                    <span class="badge bg-light text-dark">{{ $variant->name }} (+LE {{ number_format($variant->price_modifier, 2) }})</span>
                    <form action="{{ route('menu.variants.destroy', $variant) }}" method="POST" class="d-inline">@csrf @method('DELETE')
                        <button class="btn btn-sm p-0 text-danger"><i class="fas fa-times-circle"></i></button>
                    </form>
                </div>
                @endforeach
                <form method="POST" action="{{ route('menu.variants.store', $item) }}" class="d-inline-flex gap-2 align-items-center">
                    @csrf
                    <input type="text" name="name" class="form-control form-control-sm de-input" placeholder="Variant name" style="width: 120px;" required>
                    <input type="number" step="0.01" name="price_modifier" class="form-control form-control-sm de-input" placeholder="+$" style="width: 80px;" required>
                    <button type="submit" class="btn btn-sm btn-de">Add</button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
