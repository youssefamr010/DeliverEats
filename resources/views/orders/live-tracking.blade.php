<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Tracking - DeliverEats Premium</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Outfit', 'sans-serif'] },
                    colors: {
                        dark: { 900: '#030712', 800: '#0f172a', 700: '#1e293b', 600: '#334155' },
                        gold: { 400: '#facc15', 500: '#eab308', 600: '#ca8a04' },
                        brand: { 500: '#3b82f6', 600: '#2563eb' }
                    },
                    animation: {
                        'pulse-glow': 'pulseGlow 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'slide-up': 'slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards',
                        'slide-in-right': 'slideInRight 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards',
                        'bounce-dot': 'bounceDot 1.4s infinite ease-in-out both',
                        'progress': 'progressWave 2s infinite linear',
                        'text-shimmer': 'textShimmer 2.5s ease-out infinite alternate',
                        'float': 'float 6s ease-in-out infinite',
                    },
                    keyframes: {
                        pulseGlow: { '0%, 100%': { opacity: 1, filter: 'drop-shadow(0 0 15px rgba(234,179,8,0.6))' }, '50%': { opacity: .7, filter: 'drop-shadow(0 0 5px rgba(234,179,8,0.2))' } },
                        slideUp: { '0%': { opacity: 0, transform: 'translateY(30px)' }, '100%': { opacity: 1, transform: 'translateY(0)' } },
                        slideInRight: { '0%': { opacity: 0, transform: 'translateX(-30px)' }, '100%': { opacity: 1, transform: 'translateX(0)' } },
                        bounceDot: { '0%, 80%, 100%': { transform: 'scale(0)' }, '40%': { transform: 'scale(1)' } },
                        progressWave: { '0%': { backgroundPosition: '200% 0' }, '100%': { backgroundPosition: '-200% 0' } },
                        textShimmer: { '0%': { backgroundPosition: '0% 50%' }, '100%': { backgroundPosition: '100% 50%' } },
                        float: { '0%, 100%': { transform: 'translateY(0)' }, '50%': { transform: 'translateY(-10px)' } },
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #030712; color: #f8fafc; font-family: 'Outfit', sans-serif; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #030712; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #334155; }
        .leaflet-container { background: #030712 !important; }
        .leaflet-tile-pane { filter: brightness(0.6) contrast(1.2) saturate(0.5) sepia(0.2) hue-rotate(180deg); }
        .glass-panel { background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.05); }
        .shimmer-text { background: linear-gradient(to right, #eab308 20%, #fff 40%, #eab308 60%, #eab308 80%); background-size: 200% auto; color: transparent; -webkit-background-clip: text; animation: textShimmer 3s linear infinite; }
    </style>
</head>
<body class="overflow-hidden">
    <div id="root"></div>

    <script type="text/babel">
const { useState, useEffect, useRef, useCallback } = React;

const ORDERS = [
    { id: '1042-DX', customer: 'Ahmed Hassan', restaurant: 'Burger Palace', items: ['Classic Burger x2', 'French Fries', 'Coca Cola'], total: 24.47, status: 'preparing', rider: { name: 'Rider Nour', phone: '+20 112 345 6789', vehicle: 'Motorcycle', avatar: '🏍️', rating: 4.8 }, eta: 25, address: '42 Zamalek, Cairo', lat: 30.0600, lng: 31.2200 },
    { id: '1043-ZX', customer: 'Sara El-Din', restaurant: 'Pizza Roma', items: ['Margherita Large', 'Pepperoni', 'Garlic Bread'], total: 38.97, status: 'on_the_way', rider: { name: 'Rider Ahmed', phone: '+20 100 987 6543', vehicle: 'Car', avatar: '🚗', rating: 4.9 }, eta: 12, address: '15 Maadi, Cairo', lat: 30.0444, lng: 31.2357 },
    { id: '1044-KX', customer: 'Youssef Ali', restaurant: 'Koshari Corner', items: ['Koshari Classic Large', 'Karkade x2'], total: 8.47, status: 'confirmed', rider: { name: 'Rider Hassan', phone: '+20 111 222 3333', vehicle: 'Motorcycle', avatar: '🏍️', rating: 4.7 }, eta: 35, address: '33 Abbassia, Cairo', lat: 30.0710, lng: 31.2830 },
];

const STATUSES = ['placed','confirmed','preparing','ready_for_pickup','on_the_way','delivered'];
const STATUS_LABELS = { placed: 'Order Placed', confirmed: 'Confirmed', preparing: 'Preparing', ready_for_pickup: 'Ready', on_the_way: 'On the Way', delivered: 'Delivered' };
const STATUS_ICONS = { placed: 'fa-receipt', confirmed: 'fa-check-double', preparing: 'fa-fire-burner', ready_for_pickup: 'fa-box-open', on_the_way: 'fa-motorcycle', delivered: 'fa-flag-checkered' };

const ROUTE = [
    [30.0444,31.2357],[30.0460,31.2340],[30.0480,31.2310],[30.0500,31.2280],
    [30.0520,31.2260],[30.0540,31.2240],[30.0560,31.2220],[30.0580,31.2210],[30.0600,31.2200]
];

function AnimatedProgress({ status }) {
    const idx = STATUSES.indexOf(status);
    const pct = Math.min(((idx + 1) / STATUSES.length) * 100, 100);
    return (
        <div className="w-full bg-dark-900 rounded-full h-1.5 overflow-hidden border border-dark-700 mt-3">
            <div className="h-full rounded-full bg-[linear-gradient(90deg,#3b82f6,#eab308,#3b82f6)] bg-[length:200%_100%] transition-all duration-1000 ease-out animate-progress"
                 style={{ width: `${pct}%` }} />
        </div>
    );
}

function StatusTimeline({ status }) {
    const currentIdx = STATUSES.indexOf(status);
    return (
        <div className="flex justify-between items-center mt-6 relative">
            <div className="absolute left-4 right-4 top-4 h-0.5 bg-dark-700 -z-10"></div>
            {STATUSES.map((s, i) => {
                const isActive = i <= currentIdx;
                const isCurrent = i === currentIdx;
                return (
                    <div key={s} className={`flex flex-col items-center flex-1 transition-all duration-700 ${isActive ? 'opacity-100' : 'opacity-40 grayscale'}`}>
                        <div className={`w-8 h-8 rounded-full flex items-center justify-center text-sm transition-all duration-500
                            ${isActive ? 'bg-gradient-to-br from-brand-600 to-brand-400 text-white shadow-[0_0_15px_rgba(59,130,246,0.5)]' : 'bg-dark-800 text-dark-600 border border-dark-700'} 
                            ${isCurrent ? 'ring-4 ring-brand-500/30 scale-125 bg-gradient-to-br from-gold-600 to-gold-400 shadow-[0_0_20px_rgba(234,179,8,0.6)]' : ''}`}>
                            <i className={`fas ${STATUS_ICONS[s]} ${isCurrent ? 'animate-pulse' : ''}`}></i>
                        </div>
                        <span className={`text-[10px] mt-2 font-black uppercase tracking-widest ${isCurrent ? 'text-gold-400 scale-110 origin-top' : isActive ? 'text-white' : 'text-dark-600'} transition-all duration-300`}>
                            {STATUS_LABELS[s].split(' ')[0]}
                        </span>
                    </div>
                );
            })}
        </div>
    );
}

function OrderCard({ order, isActive, onClick, index }) {
    const statusColor = { placed: 'blue', confirmed: 'indigo', preparing: 'amber', ready_for_pickup: 'purple', on_the_way: 'cyan', delivered: 'emerald' };
    const color = statusColor[order.status] || 'gray';
    
    return (
        <div onClick={onClick}
             style={{ animationDelay: `${index * 150}ms` }}
             className={`p-5 rounded-2xl cursor-pointer transition-all duration-500 border animate-slide-in-right relative overflow-hidden group
                ${isActive ? 'bg-gradient-to-br from-dark-800 to-dark-900 border-gold-500/50 shadow-[0_10px_30px_-10px_rgba(234,179,8,0.3)] scale-[1.02]' : 'bg-dark-800 border-dark-700 hover:border-dark-500 hover:bg-dark-700'}`}>
            
            {isActive && <div className="absolute top-0 right-0 w-32 h-32 bg-gold-500/10 blur-[30px] rounded-full -mr-10 -mt-10 animate-pulse-glow pointer-events-none"></div>}
            
            <div className="flex justify-between items-start mb-3 relative z-10">
                <div>
                    <span className={`font-black text-xl tracking-tight ${isActive ? 'shimmer-text' : 'text-gray-300'}`}>#{order.id}</span>
                    <p className="text-sm text-gray-400 font-bold mt-1 uppercase tracking-wider">{order.restaurant}</p>
                </div>
                <div className={`px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-1.5
                    bg-${color}-500/10 text-${color}-400 border border-${color}-500/20`}>
                    <i className={`fas ${STATUS_ICONS[order.status]} ${isActive ? 'animate-bounce' : ''}`}></i>
                    {STATUS_LABELS[order.status]}
                </div>
            </div>
            
            <div className="flex justify-between items-end mt-4 relative z-10">
                <div className="flex items-center gap-2">
                    <div className="w-8 h-8 rounded-full bg-dark-900 border border-dark-600 flex items-center justify-center text-xs">
                        {order.rider.avatar}
                    </div>
                    <span className="font-bold text-sm text-gray-300">{order.rider.name}</span>
                </div>
                <span className={`font-black text-2xl ${isActive ? 'text-white' : 'text-gray-500'}`}>LE {order.total.toFixed(2)}</span>
            </div>
            {isActive && <AnimatedProgress status={order.status} />}
        </div>
    );
}

function RiderProfile({ rider }) {
    return (
        <div className="glass-panel rounded-3xl p-6 border border-dark-700 animate-slide-up hover:-translate-y-1 transition-transform duration-300 shadow-2xl relative overflow-hidden group">
            <div className="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-brand-500 via-gold-500 to-brand-500 opacity-50 group-hover:opacity-100 transition-opacity"></div>
            <h3 className="text-dark-400 font-black text-[10px] uppercase tracking-[3px] mb-5 text-gray-500">
                <i className="fas fa-id-badge mr-2"></i>Assigned Professional
            </h3>
            <div className="flex items-center gap-5">
                <div className="w-16 h-16 rounded-2xl bg-gradient-to-br from-dark-800 to-dark-900 border border-gold-500/30 flex items-center justify-center text-3xl shadow-[0_0_20px_rgba(234,179,8,0.2)] animate-float">
                    {rider.avatar}
                </div>
                <div className="flex-1">
                    <p className="font-black text-2xl text-white tracking-tight">{rider.name}</p>
                    <div className="flex items-center gap-2 mt-1">
                        <span className="px-2 py-0.5 rounded-md bg-dark-700 text-[10px] font-bold text-gray-300 uppercase tracking-wider">{rider.vehicle}</span>
                        <span className="flex items-center gap-1 text-gold-400 font-black text-sm"><i className="fas fa-star text-[10px]"></i>{rider.rating}</span>
                    </div>
                </div>
                <a href={`tel:${rider.phone}`} className="w-12 h-12 rounded-2xl bg-green-500/10 text-green-400 flex items-center justify-center hover:bg-green-500 hover:text-white transition-all hover:scale-110 hover:shadow-[0_0_20px_rgba(34,197,94,0.4)] border border-green-500/20">
                    <i className="fas fa-phone-volume"></i>
                </a>
            </div>
        </div>
    );
}

function LiveMap({ order, routeIdx }) {
    const mapRef = useRef(null);
    const markerRef = useRef(null);
    const mapInstance = useRef(null);

    useEffect(() => {
        if (!mapRef.current || mapInstance.current) return;
        const map = L.map(mapRef.current, { zoomControl: false, attributionControl: false }).setView([30.0500, 31.2300], 14);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png').addTo(map);
        
        mapInstance.current = map;
        
        // Custom animated store marker
        L.marker([order.lat, order.lng], {
            icon: L.divIcon({ html: '<div class="relative"><div class="absolute inset-0 bg-gold-500 rounded-full animate-ping opacity-75"></div><div style="background:linear-gradient(135deg,#eab308,#a16207);width:36px;height:36px;border-radius:12px;display:flex;align-items:center;justify-content:center;box-shadow:0 10px 25px rgba(234,179,8,0.5);border:2px solid #fff;position:relative;z-index:10;"><i class="fas fa-store" style="color:#fff;font-size:16px;"></i></div></div>', className: '', iconSize: [36, 36], iconAnchor: [18, 36] })
        }).addTo(map);
        
        // Destination marker
        L.marker([order.lat + 0.015, order.lng + 0.015], {
            icon: L.divIcon({ html: '<div style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);width:32px;height:32px;border-radius:10px;display:flex;align-items:center;justify-content:center;box-shadow:0 10px 25px rgba(59,130,246,0.5);border:2px solid #fff;"><i class="fas fa-map-marker-alt" style="color:#fff;font-size:14px;"></i></div>', className: '', iconSize: [32, 32], iconAnchor: [16, 32] })
        }).addTo(map);
        
        // Animated Route line
        const polyline = L.polyline(ROUTE, { color: '#3b82f6', weight: 4, opacity: 0.6, dashArray: '10 15', lineCap: 'round' }).addTo(map);
        
        // Rider marker
        markerRef.current = L.marker(ROUTE[0], {
            icon: L.divIcon({ html: '<div class="relative group"><div class="absolute inset-0 bg-brand-500 rounded-full animate-ping opacity-40"></div><div style="background:#fff;width:48px;height:48px;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 0 30px rgba(59,130,246,0.8);border:4px solid #3b82f6;position:relative;z-index:10;transition:transform 0.3s;"><i class="fas fa-motorcycle" style="color:#030712;font-size:20px;"></i></div></div>', className: '', iconSize: [48, 48], iconAnchor: [24, 24] })
        }).addTo(map);
        
        return () => { map.remove(); mapInstance.current = null; };
    }, []);

    useEffect(() => {
        if (markerRef.current && ROUTE[routeIdx]) {
            markerRef.current.setLatLng(ROUTE[routeIdx]);
            mapInstance.current?.panTo(ROUTE[routeIdx], { animate: true, duration: 1.5, easeLinearity: 0.25 });
        }
    }, [routeIdx]);

    return <div ref={mapRef} className="w-full h-full" />;
}

function App() {
    const [orders, setOrders] = useState(ORDERS);
    const [activeId, setActiveId] = useState(ORDERS[1].id);
    const [routeIdx, setRouteIdx] = useState(0);
    const [mobilePanel, setMobilePanel] = useState(false);

    useEffect(() => {
        const i = setInterval(() => setRouteIdx(r => (r + 1) % ROUTE.length), 2000);
        return () => clearInterval(i);
    }, []);

    useEffect(() => {
        const i = setInterval(() => {
            setOrders(prev => prev.map(o => {
                const idx = STATUSES.indexOf(o.status);
                if (idx < STATUSES.length - 1 && Math.random() > 0.8) {
                    return { ...o, status: STATUSES[idx + 1], eta: Math.max(0, o.eta - 3) };
                }
                return o;
            }));
        }, 5000);
        return () => clearInterval(i);
    }, []);

    const activeOrder = orders.find(o => o.id === activeId) || orders[0];

    return (
        <div className="h-screen w-screen flex flex-col lg:flex-row overflow-hidden bg-dark-900 font-sans selection:bg-gold-500 selection:text-dark-900">
            {/* Mobile Header */}
            <div className="lg:hidden flex items-center justify-between p-5 glass-panel z-50">
                <h1 className="text-white font-black text-2xl tracking-tighter"><i className="fas fa-motorcycle mr-2 text-gold-500"></i>Deliver<span className="text-brand-500">Eats</span></h1>
                <button onClick={() => setMobilePanel(!mobilePanel)} className="w-12 h-12 rounded-xl bg-dark-800 border border-dark-700 flex items-center justify-center text-white active:scale-95 transition-transform">
                    <i className={`fas fa-${mobilePanel ? 'times' : 'bars-staggered'} text-xl`}></i>
                </button>
            </div>

            {/* Sidebar List */}
            <div className={`${mobilePanel ? 'translate-x-0' : '-translate-x-full'} lg:translate-x-0 fixed lg:relative z-40 w-full lg:w-[420px] xl:w-[480px] h-full glass-panel border-r border-dark-700 flex-shrink-0 flex flex-col transition-transform duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] shadow-2xl`}>
                <div className="p-6 pb-2 hidden lg:block">
                    <a href="/" className="inline-block hover:scale-105 transition-transform origin-left">
                        <h1 className="text-3xl font-black text-white tracking-tighter drop-shadow-lg"><i className="fas fa-motorcycle mr-3 text-gold-500 animate-float inline-block"></i>Deliver<span className="text-brand-500">Eats</span></h1>
                    </a>
                    <p className="text-gray-500 text-xs font-bold mt-2 uppercase tracking-[4px]">Mission Control</p>
                </div>
                
                <div className="px-6 pt-6 pb-4 flex items-center justify-between bg-dark-900/50 mt-4 border-y border-dark-800">
                    <h2 className="text-white font-black text-sm uppercase tracking-[2px] flex items-center gap-2">
                        <div className="w-2 h-2 rounded-full bg-red-500 animate-pulse"></div> Live Operations
                    </h2>
                    <span className="bg-dark-800 text-white px-3 py-1 rounded-lg text-xs font-black border border-dark-700 shadow-inner">
                        {orders.length} ACTIVE
                    </span>
                </div>
                
                <div className="flex-1 overflow-y-auto p-4 space-y-4">
                    {orders.map((o, i) => (
                        <OrderCard key={o.id} order={o} isActive={o.id === activeId} index={i} onClick={() => { setActiveId(o.id); setMobilePanel(false); }} />
                    ))}
                    
                    <div className="flex space-x-2 justify-center items-center py-6 opacity-50">
                        {[0, 0.16, 0.32].map((d, i) => (
                            <div key={i} className="w-2 h-2 bg-gold-500 rounded-full animate-bounce-dot shadow-[0_0_10px_rgba(234,179,8,0.5)]" style={{ animationDelay: `${d}s` }} />
                        ))}
                    </div>
                </div>
            </div>

            {/* Main Map Area */}
            <div className="flex-1 relative flex flex-col overflow-hidden bg-dark-900">
                <div className="absolute inset-0 z-0 opacity-80">
                    <LiveMap order={activeOrder} routeIdx={routeIdx} />
                </div>
                
                {/* Floating Overlays */}
                <div className="absolute top-6 left-6 z-[1000] animate-slide-in-right">
                    <div className="glass-panel rounded-3xl px-6 py-4 border border-dark-700 shadow-[0_20px_40px_rgba(0,0,0,0.5)] hover:border-gold-500/30 transition-colors group">
                        <p className="text-[10px] font-black uppercase tracking-[3px] text-gray-500 mb-1 group-hover:text-gold-500 transition-colors">Target ETA</p>
                        <p className="text-5xl font-black text-white tracking-tighter drop-shadow-lg flex items-baseline gap-1">
                            {activeOrder.eta}<span className="text-xl text-gold-500 font-bold uppercase tracking-widest">min</span>
                        </p>
                    </div>
                </div>
                
                <div className="absolute top-6 right-6 z-[1000] animate-slide-in-right" style={{ animationDelay: '100ms' }}>
                    <div className="glass-panel rounded-full px-6 py-3 border border-dark-700 shadow-[0_20px_40px_rgba(0,0,0,0.5)] flex items-center gap-3">
                        <div className="relative flex h-4 w-4">
                            <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-400 opacity-75"></span>
                            <span className="relative inline-flex rounded-full h-4 w-4 bg-brand-500 shadow-[0_0_10px_rgba(59,130,246,0.8)]"></span>
                        </div>
                        <span className="text-white font-black text-sm uppercase tracking-widest">{STATUS_LABELS[activeOrder.status]}</span>
                    </div>
                </div>

                {/* Bottom Control Dashboard */}
                <div className="absolute bottom-0 left-0 right-0 z-[1000] p-4 lg:p-6 bg-gradient-to-t from-dark-900 via-dark-900/90 to-transparent pt-32">
                    <div className="max-w-7xl mx-auto">
                        <div className="glass-panel rounded-[2rem] border border-dark-700 p-6 lg:p-8 shadow-[0_-20px_50px_rgba(0,0,0,0.5)] relative overflow-hidden">
                            {/* Decorative background glow */}
                            <div className="absolute bottom-0 left-1/2 -translate-x-1/2 w-3/4 h-1/2 bg-brand-500/10 blur-[100px] pointer-events-none"></div>
                            
                            <div className="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mb-8 relative z-10">
                                <div>
                                    <h2 className="text-4xl font-black text-white tracking-tighter mb-2 shimmer-text drop-shadow-lg">Order #{activeOrder.id}</h2>
                                    <p className="text-gray-400 font-bold flex items-center gap-2 text-sm uppercase tracking-wider">
                                        <i className="fas fa-store text-gold-500"></i> {activeOrder.restaurant} 
                                        <i className="fas fa-arrow-right text-dark-600 mx-2 text-xs"></i> 
                                        <i className="fas fa-map-pin text-brand-500"></i> {activeOrder.address}
                                    </p>
                                </div>
                                <div className="text-left lg:text-right bg-dark-800/80 px-6 py-3 rounded-2xl border border-dark-700">
                                    <p className="text-gray-500 font-black text-[10px] uppercase tracking-[3px] mb-1">Total Value</p>
                                    <p className="text-3xl font-black text-white tracking-tight">LE {activeOrder.total.toFixed(2)}</p>
                                </div>
                            </div>
                            
                            <div className="relative z-10 bg-dark-900/50 rounded-2xl p-6 border border-dark-800">
                                <StatusTimeline status={activeOrder.status} />
                            </div>
                        </div>

                        {/* Additional Info Row - Hidden on small mobile */}
                        <div className="hidden md:grid grid-cols-2 gap-6 mt-6">
                            <RiderProfile rider={activeOrder.rider} />
                            
                            <div className="glass-panel rounded-3xl p-6 border border-dark-700 animate-slide-up hover:-translate-y-1 transition-transform duration-300 shadow-2xl" style={{ animationDelay: '100ms' }}>
                                <h3 className="text-dark-400 font-black text-[10px] uppercase tracking-[3px] mb-5 text-gray-500">
                                    <i className="fas fa-clipboard-list mr-2"></i>Order Manifest
                                </h3>
                                <div className="space-y-3">
                                    {activeOrder.items.map((item, i) => (
                                        <div key={i} className="flex items-center gap-3 bg-dark-800/50 p-3 rounded-xl border border-dark-700/50">
                                            <div className="w-8 h-8 rounded-lg bg-dark-700 flex items-center justify-center text-gold-500 text-xs">
                                                <i className="fas fa-check"></i>
                                            </div>
                                            <span className="text-white font-bold">{item}</span>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

ReactDOM.createRoot(document.getElementById('root')).render(<App />);
    </script>
</body>
</html>
