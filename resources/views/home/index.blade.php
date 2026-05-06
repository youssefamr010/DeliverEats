@extends('layouts.app')
@section('title', 'DeliverEats - Cairo\'s Premium Delivery')

@section('styles')
<style>
    .hero-section {
        padding: 12rem 0 10rem;
        position: relative;
    }
    
    .hero-title {
        font-size: 5.5rem;
        font-weight: 900;
        line-height: 0.95;
        letter-spacing: -4px;
        margin-bottom: 2.5rem;
    }
    
    .hero-title span {
        background: linear-gradient(135deg, var(--de-primary), var(--de-secondary));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        position: relative;
    }

    .hero-subtitle {
        font-size: 1.25rem;
        color: var(--de-text-muted);
        max-width: 550px;
        line-height: 1.7;
        margin-bottom: 3.5rem;
    }

    .glass-stat {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(10px);
        border: 1px solid var(--de-border);
        padding: 1.5rem 2.5rem;
        border-radius: 28px;
        transition: all 0.4s ease;
    }
    .glass-stat:hover {
        transform: translateY(-5px);
        background: rgba(255, 255, 255, 0.06);
    }
    .glass-stat h3 { font-size: 2.5rem; margin: 0; }
    .glass-stat p { font-size: 0.75rem; margin: 0; text-transform: uppercase; font-weight: 800; letter-spacing: 1px; }

    .section-header { margin-bottom: 5rem; }
    .section-tag {
        display: inline-block;
        background: rgba(99, 102, 241, 0.1);
        color: var(--de-primary);
        padding: 0.6rem 1.4rem;
        border-radius: 40px;
        font-weight: 800;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 2px;
        margin-bottom: 1.5rem;
        border: 1px solid rgba(99, 102, 241, 0.2);
    }

    /* Restaurant Card Enhancements */
    .restaurant-card {
        position: relative;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .restaurant-image-wrapper {
        position: relative;
        height: 220px;
        overflow: hidden;
        border-radius: 24px;
        margin: 10px;
    }
    .restaurant-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .restaurant-card:hover .restaurant-img { transform: scale(1.1); }
    
    .rating-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: rgba(15, 23, 42, 0.8);
        backdrop-filter: blur(10px);
        padding: 0.5rem 0.8rem;
        border-radius: 14px;
        font-weight: 800;
        color: var(--de-accent);
        display: flex;
        align-items: center;
        gap: 5px;
        border: 1px solid rgba(255,255,255,0.1);
    }

    .floating-icons {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        pointer-events: none;
        z-index: -1;
    }
</style>
@endsection

@section('content')

<!-- HERO SECTION -->
<section class="hero-section overflow-hidden">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <div class="animate-enter">
                    <span class="section-tag">
                        <i class="fas fa-crown me-2"></i>Cairo's Finest
                    </span>
                    <h1 class="hero-title">
                        Culinary Arts<br>
                        <span>Delivered</span> Fast
                    </h1>
                    <p class="hero-subtitle">
                        Experience the most refined delivery service in the city. From Michelin-star kitchens to local gems, we bring excellence to your door.
                    </p>
                    <div class="d-flex flex-wrap gap-4 mb-5">
                        <a href="{{ route('restaurants.index') }}" class="btn-de btn-lg px-5 py-3">
                            Start Exploring <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                        <a href="{{ route('register', ['role' => 'restaurant_owner']) }}" class="btn-de-outline btn-lg px-5 py-3">
                            Join as Partner
                        </a>
                    </div>
                </div>
                
                <div class="d-flex flex-wrap gap-4 animate-enter delay-200">
                    <div class="glass-stat">
                        <h3>{{ $stats['restaurants'] ?? 10 }}+</h3>
                        <p>Partners</p>
                    </div>
                    <div class="glass-stat">
                        <h3>{{ $stats['delivered'] ?? '2k' }}+</h3>
                        <p>Delivered</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-5 d-none d-lg-block position-relative">
                <div class="floating-food text-center animate-enter delay-300">
                    <img src="https://images.unsplash.com/photo-1504674900247-0877df9cc836?q=80&w=1000&auto=format&fit=crop" 
                         alt="Food" class="img-fluid rounded-circle shadow-lg" 
                         style="width: 450px; height: 450px; object-fit: cover; border: 15px solid rgba(255,255,255,0.03);">
                </div>
                <!-- Decorative Elements -->
                <div class="floating-icons">
                    <i class="fas fa-pizza-slice text-primary opacity-25" style="position: absolute; top: 10%; right: 0; font-size: 4rem; animation: move 15s infinite alternate;"></i>
                    <i class="fas fa-hamburger text-secondary opacity-25" style="position: absolute; bottom: 20%; left: -50px; font-size: 5rem; animation: move 20s infinite alternate-reverse;"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FEATURED RESTAURANTS -->
<section class="py-5 my-5">
    <div class="container">
        <div class="section-header text-center animate-enter">
            <span class="section-tag">Selection</span>
            <h2 class="display-4 fw-bold">Featured Kitchens</h2>
            <p class="text-muted mx-auto" style="max-width: 600px;">Hand-picked restaurants that represent the peak of Cairo's culinary scene.</p>
        </div>
        
        <div class="row g-4">
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
            @foreach($featuredRestaurants as $restaurant)
            <div class="col-lg-3 col-md-6">
                <a href="{{ route('restaurants.show', $restaurant) }}" class="text-decoration-none">
                    <div class="de-card restaurant-card">
                        <div class="restaurant-image-wrapper">
                            @php
                            $imgUrl = $restaurant->image ?? ($restaurantImages[$restaurant->name] ?? 'https://images.unsplash.com/photo-1514933651103-005eec06c04b?q=80&w=800');
                            @endphp
                            <img src="{{ $imgUrl }}" 
                                 class="restaurant-img" alt="{{ $restaurant->name }}">
                            <div class="rating-badge">
                                <i class="fas fa-star"></i> {{ number_format($restaurant->rating_avg, 1) }}
                            </div>
                        </div>
                        <div class="de-card-body pt-2 pb-4 px-4">
                            <h5 class="mb-1 text-white">{{ $restaurant->name }}</h5>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small text-muted">{{ $restaurant->cuisine_type }}</span>
                                <span class="small text-accent fw-bold">{{ $restaurant->delivery_fee }} LE</span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
        
        <div class="text-center mt-5">
            <a href="{{ route('restaurants.index') }}" class="btn-de-outline py-3 px-5">
                View All Restaurants <i class="fas fa-chevron-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- PROCESS -->
<section class="py-5 my-5 bg-black bg-opacity-25 py-5">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-4">
                <span class="section-tag">Workflow</span>
                <h2 class="display-5 fw-bold mb-4">How we deliver excellence</h2>
                <p class="text-muted fs-5">A seamless process designed for the modern lifestyle.</p>
            </div>
            <div class="col-lg-8">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="de-card p-4 text-center h-100">
                            <div class="mb-4 text-primary fs-1"><i class="fas fa-mobile-alt"></i></div>
                            <h5 class="mb-3">Order</h5>
                            <p class="small text-muted mb-0">Browse and order from the best menus in the city.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="de-card p-4 text-center h-100">
                            <div class="mb-4 text-secondary fs-1"><i class="fas fa-fire"></i></div>
                            <h5 class="mb-3">Prep</h5>
                            <p class="small text-muted mb-0">Our partners prepare your meal with absolute care.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="de-card p-4 text-center h-100">
                            <div class="mb-4 text-accent fs-1"><i class="fas fa-map-marker-alt"></i></div>
                            <h5 class="mb-3">Deliver</h5>
                            <p class="small text-muted mb-0">Track your meal live until it reaches your door.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-5 mb-5">
    <div class="container">
        <div class="de-card p-5 p-lg-5 text-center overflow-hidden position-relative">
            <div class="py-5">
                <h2 class="display-3 fw-bold text-white mb-4">Ready to taste the <span class="text-primary">Difference?</span></h2>
                <p class="text-muted fs-5 mb-5 mx-auto" style="max-width: 600px;">
                    Join thousands of satisfied customers and experience the next level of food delivery.
                </p>
                <div class="d-flex justify-content-center gap-4 flex-wrap">
                    <a href="{{ route('register') }}" class="btn-de btn-lg px-5">Join DeliverEats</a>
                    <a href="{{ route('register', ['role' => 'restaurant_owner']) }}" class="btn-de-outline btn-lg px-5">Partner with Us</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

