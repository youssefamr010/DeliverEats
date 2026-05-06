<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return $this->redirectByRole(Auth::user());
        }

        return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)],
            'role'     => 'required|in:customer,restaurant_owner,rider',
            'phone'    => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'],
            'phone'    => $validated['phone'] ?? null,
        ]);

        Auth::login($user);
        return $this->redirectByRole($user);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }

    // --- Forgot Password by Name Flow ---

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function verifyName(Request $request)
    {
        $request->validate(['name' => 'required|string']);
        $user = User::where('name', $request->name)->first();

        if (!$user) {
            return back()->withErrors(['name' => 'No account found with this name.']);
        }

        session(['reset_user_id' => $user->id]);
        return redirect()->route('password.reset.form');
    }

    public function showResetPassword()
    {
        if (!session('reset_user_id')) {
            return redirect()->route('password.request');
        }
        return view('auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $userId = session('reset_user_id');
        $user = User::findOrFail($userId);
        
        $user->password = Hash::make($request->password);
        $user->save();

        session()->forget('reset_user_id');
        Auth::login($user);

        return $this->redirectByRole($user)->with('success', 'Security credentials updated successfully.');
    }

    private function redirectByRole(User $user)
    {
        return match($user->role) {
            'admin'            => redirect()->route('admin.dashboard'),
            'restaurant_owner' => redirect()->route('restaurant.dashboard'),
            'rider'            => redirect()->route('rider.dashboard'),
            default            => redirect()->route('restaurants.index'),
        };
    }
}
