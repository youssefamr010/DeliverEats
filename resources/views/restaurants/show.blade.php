@extends('layouts.app')
@section('title', $restaurant->name . ' - DeliverEats')

@section('content')
<div class="container py-5">
    <!-- Immersive Restaurant Header -->
    @php
        $restaurantImages = [
            'Burger Palace' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=1200&auto=format&fit=crop',
            'Pizza Roma' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?q=80&w=1200&auto=format&fit=crop',
            'Dragon Wok' => 'https://images.unsplash.com/photo-1585032226651-759b368d7246?q=80&w=1200&auto=format&fit=crop',
            'Sushi Zen' => 'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?q=80&w=1200&auto=format&fit=crop',
            'Falafel King' => 'https://images.unsplash.com/photo-1565557623262-b51c2513a641?q=80&w=1200&auto=format&fit=crop',
            'Spice Garden' => 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?q=80&w=1200&auto=format&fit=crop',
            'Koshari Corner' => 'https://images.unsplash.com/photo-1533777324565-a040eb52facd?q=80&w=1200&auto=format&fit=crop',
            'Nile Bistro' => 'https://images.unsplash.com/photo-1544148103-0773bf10d330?q=80&w=1200&auto=format&fit=crop',
            'Grill Master' => 'https://images.unsplash.com/photo-1529193591184-b1d58069ecdd?q=80&w=1200&auto=format&fit=crop',
            'Sweet Bites' => 'https://images.unsplash.com/photo-1551024506-0bccd828d307?q=80&w=1200&auto=format&fit=crop',
        ];
        $imgUrl = $restaurant->image ?? ($restaurantImages[$restaurant->name] ?? 'https://images.unsplash.com/photo-1514933651103-005eec06c04b?q=80&w=1200&auto=format&fit=crop');
    @endphp

    <div class="de-card overflow-hidden mb-5 animate-enter">
        <div class="position-relative" style="height: 400px;">
            <img src="{{ $imgUrl }}" class="w-100 h-100 object-fit-cover" alt="{{ $restaurant->name }}">
            <div class="position-absolute inset-0" style="background: linear-gradient(0deg, rgba(2,6,23,0.95) 0%, rgba(2,6,23,0.4) 50%, transparent 100%);"></div>
            
            <div class="position-absolute bottom-0 start-0 p-5 w-100">
                <div class="d-flex flex-wrap align-items-end justify-content-between gap-4">
                    <div>
                        <span class="section-tag mb-3">{{ $restaurant->cuisine_type }}</span>
                        <h1 class="display-3 fw-black text-white mb-3">{{ $restaurant->name }}</h1>
                        <div class="d-flex flex-wrap gap-4 align-items-center text-white-50">
                            <span class="d-flex align-items-center gap-2">
                                <i class="fas fa-star text-accent"></i> 
                                <span class="text-white fw-bold">{{ number_format($restaurant->rating_avg, 1) }}</span> 
                                ({{ $restaurant->rating_count }} reviews)
                            </span>
                            <span class="d-flex align-items-center gap-2">
                                <i class="fas fa-clock text-primary"></i> {{ $restaurant->avg_prep_time }} min
                            </span>
                            <span class="d-flex align-items-center gap-2">
                                <i class="fas fa-motorcycle text-secondary"></i> LE {{ number_format($restaurant->delivery_fee, 0) }} delivery
                            </span>
                            @if($restaurant->is_open)
                                <span class="badge-status badge-delivered" style="background: rgba(16, 185, 129, 0.2);">Open Now</span>
                            @else
                                <span class="badge-status badge-cancelled">Closed</span>
                            @endif
                        </div>
                    </div>
                    @auth
                        @if($restaurant->is_open)
                            <a href="{{ route('orders.create', $restaurant) }}" class="btn btn-de-gold btn-lg px-5 py-3 fs-5 shadow-lg">
                                <i class="fas fa-shopping-basket me-2"></i> Start Order
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <div class="row g-5">
        <!-- Menu Side -->
        <div class="col-lg-8">
            @foreach($restaurant->categories as $category)
                @if($category->is_active && $category->menuItems->count() > 0)
                <div class="mb-5 animate-enter">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <h2 class="fw-bold text-white mb-0">{{ $category->name }}</h2>
                        <div class="flex-grow-1 border-bottom border-light border-opacity-10"></div>
                    </div>
                    
                    <div class="row g-4">
                        @foreach($category->menuItems as $item)
                        <div class="col-md-6">
                            <div class="de-card p-0 overflow-hidden">
                                <div class="d-flex">
                                    <div style="width: 140px; min-width: 140px; min-height: 160px;">
                                        @php
                                            $defaultDishImg = 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?q=80&w=400&auto=format&fit=crop';
                                            $itemImg = $item->image ?: $defaultDishImg;
                                            // Handle relative paths if any
                                            if ($item->image && !str_starts_with($item->image, 'http')) {
                                                $itemImg = asset('storage/' . $item->image);
                                            }
                                        @endphp
                                        <img src="{{ $itemImg }}" class="w-100 h-100 object-fit-cover" style="min-height: 160px;" alt="{{ $item->name }}" onerror="this.src='{{ $defaultDishImg }}'">
                                    </div>
                                    <div class="p-4 flex-grow-1">
                                        <div class="d-flex justify-content-between mb-2">
                                            <h5 class="text-white fw-bold mb-0">{{ $item->name }}</h5>
                                            <span class="text-primary fw-black">LE {{ number_format($item->base_price, 0) }}</span>
                                        </div>
                                        <p class="small text-muted mb-3 line-clamp-2">{{ $item->description }}</p>
                                        
                                        @if($item->variants->count() > 0)
                                            <div class="d-flex flex-wrap gap-2 mb-3">
                                                @foreach($item->variants as $variant)
                                                    <span class="x-small text-uppercase fw-bold text-white-50 px-2 py-1 rounded-pill" style="border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.03);">{{ $variant->name }}</span>
                                                @endforeach
                                            </div>
                                        @endif

                                        <div class="d-flex align-items-center justify-content-between">
                                            @if(!$item->is_available)
                                                <span class="x-small text-uppercase fw-black text-danger">Sold Out</span>
                                            @else
                                                <span class="x-small text-uppercase fw-bold text-success"><i class="fas fa-check-circle me-1"></i>Available</span>
                                            @endif
                                            
                                            {{-- Add button removed for immersive browsing experience --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            @endforeach
        </div>

        <!-- Sidebar Info -->
        <div class="col-lg-4">
            <div class="sticky-top" style="top: 120px;">
                <div class="de-card mb-4 animate-enter delay-200">
                    <div class="de-card-body">
                        <h5 class="text-white fw-black mb-4 d-flex align-items-center gap-2">
                            <i class="fas fa-info-circle text-primary"></i> Restaurant Info
                        </h5>
                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex gap-3">
                                <i class="fas fa-map-marker-alt text-muted mt-1"></i>
                                <div>
                                    <p class="small text-white mb-0">Location</p>
                                    <p class="small text-muted">{{ $restaurant->address }}</p>
                                </div>
                            </div>
                            <div class="d-flex gap-3">
                                <i class="fas fa-phone text-muted mt-1"></i>
                                <div>
                                    <p class="small text-white mb-0">Contact</p>
                                    <p class="small text-muted">{{ $restaurant->phone ?: 'Not available' }}</p>
                                </div>
                            </div>
                            <div class="d-flex gap-3">
                                <i class="fas fa-shopping-bag text-muted mt-1"></i>
                                <div>
                                    <p class="small text-white mb-0">Minimum Order</p>
                                    <p class="small text-muted">LE {{ number_format($restaurant->min_order_amount, 0) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="de-card animate-enter delay-300">
                    <div class="de-card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="text-white fw-black mb-0 d-flex align-items-center gap-2">
                                <i class="fas fa-star text-accent"></i> Reviews
                            </h5>
                            <a href="{{ route('restaurants.reviews', $restaurant) }}" class="small text-primary fw-bold text-decoration-none">See All</a>
                        </div>
                        
                        <div class="d-flex flex-column gap-4">
                            @forelse($restaurant->ratings->take(3) as $rating)
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small text-white fw-bold">{{ $rating->user->name }}</span>
                                    <div class="text-accent small">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fa{{ $i <= $rating->score ? 's' : 'r' }} fa-star"></i>
                                        @endfor
                                    </div>
                                </div>
                                @if($rating->review)
                                    <p class="small text-muted mb-0 italic">"{{ Str::limit($rating->review->comment, 80) }}"</p>
                                @endif
                            </div>
                            @empty
                            <p class="small text-muted text-center py-3">No reviews yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

