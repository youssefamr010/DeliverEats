<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Rider;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    /**
     * List staff members — scoped by role
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = User::query();

        if ($user->role === 'admin') {
            // Admin sees everyone
            if ($request->filled('role')) {
                $query->where('role', $request->role);
            }
            if ($request->filled('restaurant_id')) {
                $query->where('restaurant_id', $request->restaurant_id);
            }
        } else {
            // Restaurant owner sees only their staff
            $restaurant = $user->ownedRestaurant;
            if (!$restaurant) abort(404);
            $query->where('restaurant_id', $restaurant->id)
                  ->whereIn('role', ['chef', 'rider']);
        }

        $staff = $query->orderBy('role')->orderBy('name')->paginate(20);
        $restaurants = $user->role === 'admin' ? Restaurant::orderBy('name')->get() : collect();

        return view('staff.index', compact('staff', 'restaurants'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $user = auth()->user();
        $roles = $user->role === 'admin'
            ? ['chef', 'rider', 'restaurant_owner', 'customer']
            : ['chef', 'rider'];
        $restaurants = $user->role === 'admin' ? Restaurant::orderBy('name')->get() : collect();

        return view('staff.create', compact('roles', 'restaurants'));
    }

    /**
     * Store new staff member
     */
    public function store(Request $request)
    {
        $authUser = auth()->user();
        $allowedRoles = $authUser->role === 'admin'
            ? ['chef', 'rider', 'restaurant_owner', 'customer']
            : ['chef', 'rider'];

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role'     => ['required', Rule::in($allowedRoles)],
            'phone'    => 'nullable|string|max:20',
            'restaurant_id' => 'nullable|exists:restaurants,id',
            'vehicle_type'  => 'nullable|string|in:motorcycle,car,bicycle',
        ]);

        // For restaurant owners, force restaurant_id to their own
        if ($authUser->role === 'restaurant_owner') {
            $validated['restaurant_id'] = $authUser->ownedRestaurant->id;
        }

        $newUser = User::create([
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'password'      => Hash::make($validated['password']),
            'role'          => $validated['role'],
            'restaurant_id' => $validated['restaurant_id'] ?? null,
            'phone'         => $validated['phone'] ?? null,
        ]);

        // If rider, create Rider record
        if ($validated['role'] === 'rider') {
            Rider::create([
                'user_id'       => $newUser->id,
                'vehicle_type'  => $validated['vehicle_type'] ?? 'motorcycle',
                'current_lat'   => 30.0444,
                'current_lng'   => 31.2357,
                'is_online'     => false,
                'is_available'  => false,
                'rating_avg'    => 0,
                'total_deliveries' => 0,
            ]);
        }

        $redirect = $authUser->role === 'admin' ? route('admin.staff.index') : route('restaurant.staff.index');
        return redirect($redirect)->with('success', ucfirst($validated['role']) . ' "' . $validated['name'] . '" created successfully.');
    }

    /**
     * Edit form
     */
    public function edit(User $user)
    {
        $this->authorizeStaffAccess($user);
        $authUser = auth()->user();
        $roles = $authUser->role === 'admin'
            ? ['chef', 'rider', 'restaurant_owner', 'customer']
            : ['chef', 'rider'];
        $restaurants = $authUser->role === 'admin' ? Restaurant::orderBy('name')->get() : collect();

        return view('staff.edit', compact('user', 'roles', 'restaurants'));
    }

    /**
     * Update staff member
     */
    public function update(Request $request, User $user)
    {
        $this->authorizeStaffAccess($user);
        $authUser = auth()->user();
        $allowedRoles = $authUser->role === 'admin'
            ? ['chef', 'rider', 'restaurant_owner', 'customer']
            : ['chef', 'rider'];

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'role'     => ['required', Rule::in($allowedRoles)],
            'phone'    => 'nullable|string|max:20',
            'restaurant_id' => 'nullable|exists:restaurants,id',
            'vehicle_type'  => 'nullable|string|in:motorcycle,car,bicycle',
        ]);

        $user->name  = $validated['name'];
        $user->email = $validated['email'];
        $user->role  = $validated['role'];
        $user->phone = $validated['phone'] ?? null;

        if ($authUser->role === 'admin') {
            $user->restaurant_id = $validated['restaurant_id'] ?? $user->restaurant_id;
        }

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        // Update rider record if applicable
        if ($validated['role'] === 'rider' && $user->rider) {
            $user->rider->update(['vehicle_type' => $validated['vehicle_type'] ?? $user->rider->vehicle_type]);
        } elseif ($validated['role'] === 'rider' && !$user->rider) {
            Rider::create([
                'user_id' => $user->id, 'vehicle_type' => $validated['vehicle_type'] ?? 'motorcycle',
                'current_lat' => 30.0444, 'current_lng' => 31.2357,
                'is_online' => false, 'is_available' => false, 'rating_avg' => 0, 'total_deliveries' => 0,
            ]);
        }

        $redirect = $authUser->role === 'admin' ? route('admin.staff.index') : route('restaurant.staff.index');
        return redirect($redirect)->with('success', 'Staff member updated.');
    }

    /**
     * Delete staff member
     */
    public function destroy(User $user)
    {
        $this->authorizeStaffAccess($user);
        $name = $user->name;
        $user->delete();

        $authUser = auth()->user();
        $redirect = $authUser->role === 'admin' ? route('admin.staff.index') : route('restaurant.staff.index');
        return redirect($redirect)->with('success', "\"$name\" has been deleted.");
    }

    /**
     * Authorization guard
     */
    private function authorizeStaffAccess(User $targetUser): void
    {
        $authUser = auth()->user();

        if ($authUser->role === 'admin') return; // Admin can access anyone

        // Restaurant owner can only access their own restaurant's staff
        $restaurant = $authUser->ownedRestaurant;
        if (!$restaurant || $targetUser->restaurant_id !== $restaurant->id) {
            abort(403, 'You can only manage your own restaurant staff.');
        }
    }
}
