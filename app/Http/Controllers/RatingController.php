<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\Review;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Rider;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function store(Request $request, Order $order)
    {
        $validated = $request->validate([
            'restaurant_score' => 'required|integer|min:1|max:5',
            'rider_score'      => 'nullable|integer|min:1|max:5',
            'restaurant_comment' => 'nullable|string|max:1000',
            'rider_comment'    => 'nullable|string|max:1000',
        ]);

        // Rate restaurant
        $restaurantRating = Rating::updateOrCreate(
            ['user_id' => auth()->id(), 'order_id' => $order->id, 'rateable_type' => Restaurant::class, 'rateable_id' => $order->restaurant_id],
            ['score' => $validated['restaurant_score']]
        );

        if (!empty($validated['restaurant_comment'])) {
            Review::updateOrCreate(
                ['rating_id' => $restaurantRating->id],
                ['comment' => $validated['restaurant_comment']]
            );
        }

        $order->restaurant->updateRatingAverage();

        // Rate rider
        if ($order->rider_id && !empty($validated['rider_score'])) {
            $riderRating = Rating::updateOrCreate(
                ['user_id' => auth()->id(), 'order_id' => $order->id, 'rateable_type' => Rider::class, 'rateable_id' => $order->rider_id],
                ['score' => $validated['rider_score']]
            );

            if (!empty($validated['rider_comment'])) {
                Review::updateOrCreate(
                    ['rating_id' => $riderRating->id],
                    ['comment' => $validated['rider_comment']]
                );
            }

            $order->rider->updateRatingAverage();
        }

        return back()->with('success', 'Thank you for your rating!');
    }

    public function restaurantReviews(Restaurant $restaurant)
    {
        $ratings = $restaurant->ratings()->with(['review', 'user', 'order'])->latest()->paginate(10);
        return view('ratings.restaurant', compact('restaurant', 'ratings'));
    }

    public function respond(Request $request, Review $review)
    {
        $rating = $review->rating;
        // Verify ownership
        if ($rating->rateable_type === Restaurant::class) {
            $restaurant = $rating->rateable;
            if ($restaurant->owner_id !== auth()->id() && auth()->user()->role !== 'admin') {
                abort(403);
            }
        } elseif (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate(['response' => 'required|string|max:1000']);

        $review->update([
            'response' => $validated['response'],
            'responded_at' => now()
        ]);

        return back()->with('success', 'Response submitted!');
    }
}
