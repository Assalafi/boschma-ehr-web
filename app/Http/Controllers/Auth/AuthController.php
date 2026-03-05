<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $credentials = $request->only('email', 'password');
        
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // Block users with only Admin role
            $roles = $user->roles->pluck('name')->toArray();
            $adminRoles = ['Admin', 'Super Admin', 'Administrator'];
            $nonAdminRoles = array_diff($roles, $adminRoles);
            
            if (empty($nonAdminRoles) && !empty(array_intersect($roles, $adminRoles))) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors([
                    'email' => 'Admin login is currently disabled. Please contact support.',
                ])->withInput($request->only('email'));
            }
            
            // Redirect based on user role
            return $this->redirectBasedOnRole($user);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email'));
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }

    /**
     * Redirect user based on their role
     */
    protected function redirectBasedOnRole($user)
    {
        // Skip admin dashboard — admin login is currently disabled
        // if ($user->isAdmin()) {
        //     return redirect()->route('admin.dashboard');
        // }
        
        if ($user->isDoctor()) {
            return redirect()->route('doctor.dashboard');
        }
        
        if ($user->isNurse()) {
            return redirect()->route('nurse.dashboard');
        }
        
        if ($user->isPharmacist()) {
            return redirect()->route('pharmacy.dashboard');
        }
        
        if ($user->isLabTechnician()) {
            return redirect()->route('laboratory.dashboard');
        }
        
        if ($user->isReceptionist()) {
            return redirect()->route('receptionist.dashboard');
        }
        
        // Default dashboard
        return redirect()->route('dashboard');
    }
}
