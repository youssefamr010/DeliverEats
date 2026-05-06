<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="DeliverEats - Premium Food Delivery Platform. Order from Cairo's finest restaurants.">
    <title>@yield('title', 'DeliverEats - Food Delivery')</title>

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">

    <!-- Frameworks -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        :root {
            --de-bg-obsidian: #020617;
            --de-bg-navy: #0f172a;
            --de-primary: #6366f1;
            --de-accent: #fbbf24;
            --de-secondary: #8b5cf6;
            --de-text-main: #f8fafc;
            --de-text-muted: #94a3b8;
            --de-text-dark: #64748b;
            --de-border: rgba(255, 255, 255, 0.06);
            --de-glass: rgba(15, 23, 42, 0.6);
            --de-shadow: 0 20px 50px -12px rgba(0, 0, 0, 0.5);
            --de-radius: 28px;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--de-bg-obsidian) !important;
            color: var(--de-text-main) !important;
            min-height: 100vh;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        /* Animated Mesh Background - Tornado Effect */
        .bg-mesh {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
            background: var(--de-bg-obsidian);
        }

        .mesh-blob {
            position: absolute;
            width: 80vw;
            height: 80vw;
            border-radius: 50%;
            filter: blur(150px);
            opacity: 0.12;
            animation: tornado 20s linear infinite;
        }

        .blob-1 {
            background: #a855f7; /* Purple */
            top: -20%;
            left: -20%;
            animation-duration: 25s;
        }

        .blob-2 {
            background: #64748b; /* Grey */
            bottom: -20%;
            right: -20%;
            animation-duration: 35s;
            animation-direction: reverse;
        }

        .blob-3 {
            background: #7c3aed; /* Deep Purple */
            top: 20%;
            left: 20%;
            width: 60vw;
            height: 60vw;
            animation-duration: 15s;
            opacity: 0.08;
        }

        @keyframes tornado {
            0% { transform: translate(0, 0) rotate(0deg) scale(1); }
            33% { transform: translate(10%, 10%) rotate(120deg) scale(1.2); }
            66% { transform: translate(-10%, 5%) rotate(240deg) scale(0.8); }
            100% { transform: translate(0, 0) rotate(360deg) scale(1); }
        }

        h1, h2, h3, h4, h5, h6 { color: #fff !important; font-weight: 800; letter-spacing: -0.03em; }
        p, span, .text-muted, .small { color: var(--de-text-muted) !important; }

        /* Navbar */
        .de-navbar {
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(25px) saturate(180%);
            -webkit-backdrop-filter: blur(25px) saturate(180%);
            border-bottom: 1px solid var(--de-border);
            padding: 1.25rem 0;
            position: sticky;
            top: 0;
            z-index: 1050;
        }

        .navbar-brand {
            font-weight: 900;
            font-size: 2.2rem;
            color: #fff !important;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            letter-spacing: -1.5px;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }
        .navbar-brand i { 
            background: linear-gradient(135deg, var(--de-primary), #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            transition: all 0.6s cubic-bezier(0.68, -0.6, 0.32, 1.6);
            filter: drop-shadow(0 0 8px rgba(99, 102, 241, 0.3));
        }
        .navbar-brand:hover {
            letter-spacing: 1px;
            transform: scale(1.02);
        }
        .navbar-brand:hover i {
            transform: rotate(360deg) scale(1.2);
            filter: drop-shadow(0 0 15px rgba(168, 85, 247, 0.6));
        }
        .navbar-brand span {
            position: relative;
            z-index: 1;
        }
        .navbar-brand::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--de-primary), var(--de-secondary));
            transition: width 0.4s ease;
            border-radius: 2px;
        }
        .navbar-brand:hover::after {
            width: 100%;
        }

        .nav-link {
            color: var(--de-text-muted) !important;
            font-weight: 600;
            padding: 0.6rem 1.4rem !important;
            border-radius: 16px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 0.95rem;
        }
        .nav-link:hover, .nav-link.active {
            color: #fff !important;
            background: rgba(255, 255, 255, 0.06);
            transform: translateY(-1px);
        }

        /* Cards - Modern Glassmorphism */
        .de-card {
            background: rgba(15, 23, 42, 0.6); /* Darker Grey/Obsidian */
            backdrop-filter: blur(20px);
            border: 1px solid var(--de-border);
            border-radius: var(--de-radius);
            overflow: hidden;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--de-shadow);
        }
        .de-card:hover {
            transform: translateY(-8px) scale(1.005);
            border-color: rgba(255, 255, 255, 0.12);
            background: rgba(15, 23, 42, 0.8);
            box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.7);
        }
        .de-card-body { padding: 2.5rem; }

        /* Tables - Dark Theme */
        .table {
            --bs-table-bg: transparent !important;
            --bs-table-color: #94a3b8 !important;
            --bs-table-border-color: rgba(255, 255, 255, 0.05) !important;
            margin-bottom: 0;
        }

        .table thead th {
            background: rgba(0, 0, 0, 0.2) !important;
            color: #475569 !important;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            font-weight: 800;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
        }

        .table td {
            color: #94a3b8 !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03) !important;
            background: transparent !important;
        }

        .table-hover tbody tr:hover td {
            background: rgba(255, 255, 255, 0.02) !important;
            color: #fff !important;
        }

        /* Buttons */
        .btn-de {
            background: linear-gradient(135deg, #fff 0%, #f1f5f9 100%);
            color: #000;
            border: none;
            padding: 0.9rem 2.2rem;
            border-radius: 18px;
            font-weight: 700;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 20px -5px rgba(255, 255, 255, 0.1);
        }
        .btn-de:hover {
            background: var(--de-accent);
            color: #000;
            transform: translateY(-5px);
            box-shadow: 0 15px 30px -5px rgba(251, 191, 36, 0.3);
        }

        .btn-de-outline {
            background: rgba(255, 255, 255, 0.03);
            color: #fff;
            border: 1px solid var(--de-border);
            padding: 0.9rem 2.2rem;
            border-radius: 18px;
            font-weight: 700;
            transition: all 0.4s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
        }
        .btn-de-outline:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-3px);
        }

        .btn-de-gold {
            background: linear-gradient(135deg, var(--de-accent), #f59e0b);
            color: #000;
            border: none;
            padding: 0.9rem 2.2rem;
            border-radius: 18px;
            font-weight: 700;
            transition: all 0.4s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-de-gold:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px -5px rgba(245, 158, 11, 0.4);
            filter: brightness(1.1);
        }

        /* Status Badges */
        .badge-status {
            padding: 0.5rem 1.2rem;
            border-radius: 30px;
            font-weight: 700;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: 1px solid transparent;
        }
        .badge-placed { background: rgba(148, 163, 184, 0.1); color: #94a3b8; border-color: rgba(148, 163, 184, 0.2); }
        .badge-preparing { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border-color: rgba(245, 158, 11, 0.2); }
        .badge-delivered { background: rgba(16, 185, 129, 0.1); color: #10b981; border-color: rgba(16, 185, 129, 0.2); }

        /* Form Controls */
        .de-input {
            background: rgba(15, 23, 42, 0.4) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: #94a3b8 !important; /* Dark Grey Text */
            border-radius: 16px !important;
            padding: 1rem 1.25rem !important;
            transition: all 0.3s ease !important;
        }
        .de-input::placeholder {
            color: #475569 !important;
        }
        .de-input:focus {
            background: rgba(15, 23, 42, 0.6) !important;
            border-color: var(--de-primary) !important;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.2) !important;
            color: #f1f5f9 !important;
        }

        /* Footer */
        .de-footer {
            background: rgba(2, 6, 23, 0.8);
            backdrop-filter: blur(20px);
            border-top: 1px solid var(--de-border);
            padding: 6rem 0 3rem;
            margin-top: auto;
        }

        ::-webkit-scrollbar { width: 10px; }
        ::-webkit-scrollbar-track { background: var(--de-bg-obsidian); }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 10px; border: 3px solid var(--de-bg-obsidian); }
    </style>
    @yield('styles')
    @livewireStyles
</head>
<body class="d-flex flex-column min-h-screen">
    <!-- Mesh Background -->
    <div class="bg-mesh">
        <div class="mesh-blob blob-1"></div>
        <div class="mesh-blob blob-2"></div>
        <div class="mesh-blob blob-3"></div>
    </div>

    <nav class="de-navbar">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <a class="navbar-brand" href="{{ route('home') }}">
                    <i class="fas fa-utensils"></i>
                    <span>DeliverEats</span>
                </a>

                <div class="d-none d-lg-flex align-items-center gap-2">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
                    <a class="nav-link {{ request()->routeIs('restaurants.*') ? 'active' : '' }}" href="{{ route('restaurants.index') }}">Restaurants</a>
                    
                    @auth
                        @if(auth()->user()->role === 'customer')
                            <a class="nav-link {{ request()->routeIs('orders.history') ? 'active' : '' }}" href="{{ route('orders.history') }}">My Orders</a>
                            <a class="nav-link {{ request()->routeIs('wallet.*') ? 'active' : '' }}" href="{{ route('wallet.index') }}"><i class="fas fa-wallet me-1 small"></i> Wallet</a>
                        @elseif(auth()->user()->role === 'restaurant_owner')
                            <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
                        @elseif(auth()->user()->role === 'rider')
                            <a class="nav-link" href="{{ route('rider.dashboard') }}">Rider Portal</a>
                        @elseif(auth()->user()->role === 'admin')
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
                            <a class="nav-link {{ request()->routeIs('admin.payments') ? 'active' : '' }}" href="{{ route('admin.payments') }}">Payments</a>
                            <a class="nav-link" href="{{ route('admin.liveMap') }}">Live Map</a>
                        @endif
                    @endauth
                </div>

                <div class="d-flex align-items-center gap-3">
                    @guest
                        <a href="{{ route('login') }}" class="nav-link">Login</a>
                        <a href="{{ route('register') }}" class="btn-de">Sign Up</a>
                    @else
                        <div class="dropdown">
                            <button class="btn-de-outline py-2 px-3 dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle"></i>
                                <span>{{ auth()->user()->name }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end p-2 mt-2 border-light border-opacity-10 shadow-lg" style="background: var(--de-bg-navy); border-radius: 12px;">
                                <li><a class="dropdown-item rounded-2 py-2" href="{{ route('dashboard') }}"><i class="fas fa-th-large me-2"></i>Dashboard</a></li>
                                <li><hr class="dropdown-divider opacity-10"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item rounded-2 py-2 text-danger"><i class="fas fa-sign-out-alt me-2"></i>Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @endguest
                    
                    <button class="btn d-lg-none text-white fs-4" type="button" data-bs-toggle="collapse" data-bs-target="#mobileNav">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>

            <div class="collapse d-lg-none mt-3" id="mobileNav">
                <div class="d-flex flex-column gap-2 pb-3">
                    <a class="nav-link" href="{{ route('home') }}">Home</a>
                    <a class="nav-link" href="{{ route('restaurants.index') }}">Restaurants</a>
                    @auth
                        <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow-1">
        @if(session('success'))
            <div class="container mt-4">
                <div class="alert alert-success de-card py-3 px-4 border-success border-opacity-25 animate-enter">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="container mt-4">
                <div class="alert alert-danger de-card py-3 px-4 border-danger border-opacity-25 animate-enter">
                    <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="de-footer">
        <div class="container">
            <div class="row gy-5">
                <div class="col-lg-4">
                    <a class="navbar-brand mb-4 d-flex" href="#">
                        <i class="fas fa-utensils me-2"></i> DeliverEats
                    </a>
                    <p class="text-muted pe-lg-5">Experience Cairo's most refined culinary delivery service. Precision, speed, and flavor, delivered.</p>
                </div>
                <div class="col-lg-2 col-6">
                    <h6 class="text-white fw-bold mb-4 text-uppercase">Platform</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('restaurants.index') }}" class="footer-link">Restaurants</a></li>
                        <li><a href="{{ route('register', ['role' => 'rider']) }}" class="footer-link">Become a Rider</a></li>
                        <li><a href="{{ route('register', ['role' => 'restaurant_owner']) }}" class="footer-link">Partner with Us</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-6">
                    <h6 class="text-white fw-bold mb-4 text-uppercase">Support</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('help') }}" class="footer-link">Help Center</a></li>
                        <li><a href="{{ route('terms') }}" class="footer-link">Terms of Service</a></li>
                        <li><a href="{{ route('privacy') }}" class="footer-link">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h6 class="text-white fw-bold mb-4 text-uppercase">Premium Choice</h6>
                    <p class="text-muted small">DeliverEats is Cairo's dedicated platform for high-end culinary delivery. We prioritize quality, speed, and the overall experience of our gourmet community.</p>
                </div>
            </div>
            <div class="border-top border-light border-opacity-10 mt-5 pt-4 text-center text-muted small">
                &copy; {{ date('Y') }} DeliverEats. Cairo's Premium Choice.
            </div>
        </div>
    </footer>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @livewireScripts
    @yield('scripts')
    <canvas id="interactive-lines" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; pointer-events: none; opacity: 0.3;"></canvas>

    <script>
        const canvas = document.getElementById('interactive-lines');
        const ctx = canvas.getContext('2d');
        let width, height;
        let mouse = { x: -1000, y: -1000 };

        function resize() {
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;
        }

        window.addEventListener('resize', resize);
        window.addEventListener('mousemove', e => {
            mouse.x = e.clientX;
            mouse.y = e.clientY;
        });

        resize();

        class Line {
            constructor(y) {
                this.y = y;
                this.points = [];
                this.count = 5;
                this.step = width / (this.count - 1);
                for (let i = 0; i < this.count; i++) {
                    this.points.push({
                        x: i * this.step,
                        y: y,
                        originY: y,
                        vx: 0,
                        vy: 0
                    });
                }
            }

            update() {
                this.points.forEach(p => {
                    let dx = mouse.x - p.x;
                    let dy = mouse.y - p.y;
                    let dist = Math.sqrt(dx * dx + dy * dy);
                    let force = Math.max(0, (200 - dist) / 200);
                    
                    p.vy += (p.originY - p.y) * 0.05; // spring back
                    p.vy += (mouse.y - p.y) * force * 0.1; // follow mouse
                    p.y += p.vy;
                    p.vy *= 0.9; // friction
                });
            }

            draw() {
                ctx.beginPath();
                ctx.moveTo(this.points[0].x, this.points[0].y);
                for (let i = 0; i < this.points.length - 1; i++) {
                    let p1 = this.points[i];
                    let p2 = this.points[i + 1];
                    let cx = (p1.x + p2.x) / 2;
                    let cy = (p1.y + p2.y) / 2;
                    ctx.quadraticCurveTo(p1.x, p1.y, cx, cy);
                }
                ctx.strokeStyle = 'rgba(203, 213, 225, 0.4)'; // Lighter Grey
                ctx.lineWidth = 3; // Bolder
                ctx.stroke();
            }
        }

        const lines = [];
        for (let i = 0; i < 15; i++) {
            lines.push(new Line(height * (i / 14)));
        }

        function animate() {
            ctx.clearRect(0, 0, width, height);
            lines.forEach(line => {
                line.update();
                line.draw();
            });
            requestAnimationFrame(animate);
        }

        animate();
    </script>
</body>
</html>
