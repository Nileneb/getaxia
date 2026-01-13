<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Show the login form (for Fortify compatibility).
     */
    public function showLoginForm()
    {
        return view('auth.login-existing');
    }

    /**
     * Handle registration request.
     */
    public function store(Request $request)
    {
        // Check if user is trying to log in as guest
        if ($request->has('is_guest') && $request->input('is_guest')) {
            // Find or create the single guest user
            $user = User::firstOrCreate(
                ['email' => 'guest@getaxia.de'],
                [
                    'first_name' => 'Guest',
                    'last_name' => 'User',
                    'is_guest' => true,
                    'password' => Hash::make('guest-not-allowed-to-login'),
                    'n8n_webhook_url' => 'https://n8n.getaxia.de/webhook/d2336f92-eb51-4b66-b92d-c9e7d9cf4b7d',
                    'chart_webhook_url' => 'https://n8n.getaxia.de/webhook/c3352634-be98-4448-903a-d04ed64ea90b',
                ]
            );
            
            // Reset guest user data (delete old company, goals, runs)
            if ($user->company) {
                $user->company->delete();
            }
            
            // Create fresh company for guest
            Company::create([
                'owner_user_id' => $user->id,
            ]);

            Auth::login($user);

            return redirect()->route('onboarding');
        }

        // Regular registration
        $request->validate([
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        // Create new user
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_guest' => false,
        ]);

        // Create company for the user
        Company::create([
            'owner_user_id' => $user->id,
        ]);

        Auth::login($user);

        // Redirect to onboarding
        return redirect()->route('onboarding');
    }
}

