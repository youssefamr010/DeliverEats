<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\ItemVariant;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    /**
     * Browse all restaurants (customer view)
     */
    public function index(Request $request)
    {
        $query = Restaurant::where('is_active', true);

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('cuisine_type', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->filled('cuisine')) {
            $query->where('cuisine_type', $request->cuisine);
        }

        $restaurants = $query->orderByDesc('rating_avg')->paginate(12);
        $cuisines = Restaurant::where('is_active', true)->distinct()->pluck('cuisine_type')->filter();

        return view('restaurants.index', compact('restaurants', 'cuisines'));
    }

    /**
     * Show single restaurant with menu (customer view)
     */
    public function show(Restaurant $restaurant)
    {
        $restaurant->load(['categories.menuItems.variants', 'ratings.review', 'ratings.user']);
        return view('restaurants.show', compact('restaurant'));
    }

    /**
     * Restaurant owner dashboard
     */
    public function dashboard()
    {
        $user = auth()->user();
        $restaurant = $user->restaurant;

        if (!$restaurant) {
            return redirect()->route('restaurant.create');
        }

        $restaurant->load(['categories.menuItems']);

        $todayOrders = Order::where('restaurant_id', $restaurant->id)
            ->whereDate('created_at', today())->get();

        $pendingOrders = Order::where('restaurant_id', $restaurant->id)
            ->whereIn('status', ['placed', 'confirmed', 'preparing', 'ready_for_pickup'])
            ->latest()->get();

        $paymentService = new PaymentService();
        $revenue = $paymentService->getRestaurantRevenue($restaurant, 'today');

        return view('restaurants.dashboard', compact('restaurant', 'todayOrders', 'pendingOrders', 'revenue'));
    }

    /**
     * Show restaurant creation form
     */
    public function create()
    {
        return view('restaurants.create');
    }

    /**
     * Store new restaurant
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'address'      => 'required|string|max:500',
            'phone'        => 'nullable|string|max:20',
            'cuisine_type' => 'nullable|string|max:100',
            'lat'          => 'nullable|numeric',
            'lng'          => 'nullable|numeric',
            'opens_at'     => 'nullable|date_format:H:i',
            'closes_at'    => 'nullable|date_format:H:i',
            'image'        => 'nullable|url|max:500',
        ]);

        $validated['owner_id'] = auth()->id();

        $restaurant = Restaurant::create($validated);

        return redirect()->route('restaurant.dashboard')->with('success', 'Restaurant created successfully!');
    }

    /**
     * Edit restaurant
     */
    public function edit(Restaurant $restaurant)
    {
        $this->authorizeOwner($restaurant);
        return view('restaurants.edit', compact('restaurant'));
    }

    /**
     * Update restaurant
     */
    public function update(Request $request, Restaurant $restaurant)
    {
        $this->authorizeOwner($restaurant);

        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'address'      => 'required|string|max:500',
            'phone'        => 'nullable|string|max:20',
            'cuisine_type' => 'nullable|string|max:100',
            'is_open'      => 'boolean',
            'opens_at'     => 'nullable|date_format:H:i',
            'closes_at'    => 'nullable|date_format:H:i',
            'image'        => 'nullable|url|max:500',
        ]);

        $restaurant->update($validated);

        return redirect()->route('restaurant.dashboard')->with('success', 'Restaurant updated!');
    }

    /**
     * Toggle restaurant open/closed
     */
    public function toggleOpen(Restaurant $restaurant)
    {
        $this->authorizeOwner($restaurant);
        $restaurant->update(['is_open' => !$restaurant->is_open]);
        return back()->with('success', 'Restaurant status updated!');
    }

    private function authorizeOwner(Restaurant $restaurant)
    {
        if ($restaurant->owner_id !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403);
        }
    }
}
