@extends('layouts.app')
@section('title', 'Restaurants - DeliverEats')

@section('styles')
<style>
    .restaurant-card {
        height: 100%;
        display: flex;
        flex-direction: column;
        transition: all 0.4s ease;
    }
    .image-container {
        height: 200px;
        position: relative;
        overflow: hidden;
        border-radius: 20px 20px 0 0;
    }
    .restaurant-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s ease;
    }
    .restaurant-card:hover .restaurant-img { transform: scale(1.1); }
    
    .status-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.6);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
    }

    .cuisine-badge {
        position: absolute;
        bottom: 15px;
        left: 15px;
        background: rgba(15, 23, 42, 0.7);
        backdrop-filter: blur(8px);
        padding: 0.4rem 0.8rem;
        border-radius: 12px;
        color: #fff;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        border: 1px solid rgba(255,255,255,0.1);
    }
</style>
@endsection

@section('content')
<div class="container py-5">
    <div class="row align-items-center mb-5 animate-enter">
        <div class="col-lg-6">
            <h1 class="display-4 fw-bold mb-2">Explore Kitchens</h1>
            <p class="text-muted fs-5">Find the perfect meal from Cairo's most exclusive partners.</p>
        </div>
        <div class="col-lg-6 text-lg-end">
            <div class="d-inline-flex gap-3 glass-stat py-2 px-4">
                <div class="text-center pe-4 border-end border-light border-opacity-10">
                    <h5 class="mb-0 text-white fw-black">{{ count($restaurants) }}</h5>
                    <p class="x-small text-uppercase mb-0 opacity-50">Venues</p>
                </div>
                <div class="text-center">
                    <h5 class="mb-0 text-white fw-black">{{ count($cuisines) }}</h5>
                    <p class="x-small text-uppercase mb-0 opacity-50">Cuisines</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Search & Filters -->
    <div class="de-card mb-5 animate-enter delay-100">
        <div class="de-card-body p-4">
            <form method="GET" class="row g-3">
                <div class="col-lg-6">
                    <div class="position-relative">
                        <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" name="search" class="form-control de-input ps-5" 
                               placeholder="Search for restaurants, dishes or cuisines..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-lg-3">
                    <select name="cuisine" class="form-select de-input">
                        <option value="">All Categories</option>
                        @foreach($cuisines as $cuisine)
                            <option value="{{ $cuisine }}" {{ request('cuisine') == $cuisine ? 'selected' : '' }}>{{ $cuisine }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3">
                    <div class="d-flex gap-2 h-100">
                        <button type="submit" class="btn btn-de-gold w-100">Filter</button>
                        @if(request()->hasAny(['search', 'cuisine']))
                            <a href="{{ route('restaurants.index') }}" class="btn btn-de-outline"><i class="fas fa-times"></i></a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Restaurant Grid -->
    @php
        $restaurantImages = [
            'Burger Palace' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=600&auto=format&fit=crop',
            'Pizza Roma' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?q=80&w=600&auto=format&fit=crop',
            'Dragon Wok' => 'https://images.unsplash.com/photo-1585032226651-759b368d7246?q=80&w=600&auto=format&fit=crop',
            'Sushi Zen' => 'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?q=80&w=600&auto=format&fit=crop',
            'Falafel King' => 'https://images.unsplash.com/photo-1565557623262-b51c2513a641?q=80&w=600&auto=format&fit=crop',
            'Spice Garden' => 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?q=80&w=600&auto=format&fit=crop',
            'Koshari Corner' => 'https://images.unsplash.com/photo-1533777324565-a040eb52facd?q=80&w=600&auto=format&fit=crop',
            'Nile Bistro' => 'https://images.unsplash.com/photo-1544148103-0773bf10d330?q=80&w=600&auto=format&fit=crop',
            'Grill Master' => 'https://images.unsplash.com/photo-1529193591184-b1d58069ecdd?q=80&w=600&auto=format&fit=crop',
            'Sweet Bites' => 'https://images.unsplash.com/photo-1551024506-0bccd828d307?q=80&w=600&auto=format&fit=crop',
        ];
    @endphp
    <div class="row g-4 animate-enter delay-200">
        @forelse($restaurants as $restaurant)
        <div class="col-lg-4 col-md-6">
            <a href="{{ route('restaurants.show', $restaurant) }}" class="text-decoration-none">
                <div class="de-card restaurant-card">
                    <div class="image-container">
                        @php
                            $imgUrl = $restaurant->image ?? ($restaurantImages[$restaurant->name] ?? 'https://images.unsplash.com/photo-1514933651103-005eec06c04b?q=80&w=800');
                        @endphp
                        <img src="{{ $imgUrl }}" class="restaurant-img" alt="{{ $restaurant->name }}">
                        @if(!$restaurant->is_open)
                            <div class="status-overlay">
                                <span class="badge bg-danger px-4 py-2 text-uppercase fw-black" style="letter-spacing: 2px;">Closed</span>
                            </div>
                        @endif
                        <span class="cuisine-badge">{{ $restaurant->cuisine_type }}</span>
                        <div class="rating-badge m-0" style="position: absolute; top: 15px; right: 15px;">
                            <i class="fas fa-star"></i> {{ number_format($restaurant->rating_avg, 1) }}
                        </div>
                    </div>
                    <div class="de-card-body px-4 py-4">
                        <h4 class="text-white mb-3">{{ $restaurant->name }}</h4>
                        <div class="d-flex align-items-center gap-4 text-muted small">
                            <span class="d-flex align-items-center gap-2">
                                <i class="fas fa-clock text-primary"></i> {{ $restaurant->avg_prep_time }}m
                            </span>
                            <span class="d-flex align-items-center gap-2">
                                <i class="fas fa-motorcycle text-secondary"></i> LE {{ number_format($restaurant->delivery_fee, 0) }}
                            </span>
                        </div>
                        <div class="mt-4 d-flex justify-content-between align-items-center">
                            <span class="text-white fw-bold">View Menu</span>
                            <div class="btn btn-sm btn-de-outline p-0 rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                <i class="fas fa-arrow-right small"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <div class="mb-4 opacity-10"><i class="fas fa-utensils" style="font-size: 6rem;"></i></div>
            <h3 class="text-white">No results found</h3>
            <p class="text-muted">Try refining your search or filters.</p>
        </div>
        @endforelse
    </div>

    <div class="mt-5 d-flex justify-content-center">
        {{ $restaurants->links() }}
    </div>
</div>
@endsection

