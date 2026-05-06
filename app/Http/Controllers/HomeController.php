<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Models\Order;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $featuredRestaurants = Restaurant::where('is_active', true)
            ->orderByDesc('rating_avg')
            ->take(8)
            ->get();

        $stats = [
            'restaurants' => Restaurant::where('is_active', true)->count(),
            'orders'      => Order::count(),
            'delivered'   => Order::where('status', 'delivered')->count(),
        ];

        return view('home.index', compact('featuredRestaurants', 'stats'));
    }

    public function help()
    {
        return view('pages.help');
    }

    public function privacy()
    {
        return view('pages.privacy');
    }

    public function terms()
    {
        return view('pages.terms');
    }
}
