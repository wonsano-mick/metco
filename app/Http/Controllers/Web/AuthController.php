<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\Eloquent\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // public function login(Request $request)
    // {
    //     // Debug: Log the request
    //     Log::info('Login attempt', [
    //         'email' => $request->email,
    //         'ip' => $request->ip(),
    //         'user_agent' => $request->userAgent()
    //     ]);

    //     // Validate the request
    //     $validated = $request->validate([
    //         'email' => 'required|email',
    //         'password' => 'required',
    //     ]);

    //     // Find user by email
    //     $user = User::where('email', $request->email)->first();

    //     if (!$user) {
    //         Log::warning('Login failed: User not found', ['email' => $request->email]);
    //         return back()->withErrors([
    //             'email' => 'The provided credentials do not match our records.',
    //         ])->onlyInput('email');
    //     }

    //     // Check password
    //     if (!Hash::check($request->password, $user->password)) {
    //         Log::warning('Login failed: Invalid password', ['email' => $request->email]);
    //         return back()->withErrors([
    //             'email' => 'The provided credentials do not match our records.',
    //         ])->onlyInput('email');
    //     }

    //     // Check user status
    //     if ($user->status !== 'active') {
    //         Log::warning('Login failed: User not active', [
    //             'email' => $request->email,
    //             'status' => $user->status
    //         ]);
    //         return back()->withErrors([
    //             'email' => 'Your account is ' . $user->status . '. Please contact support.',
    //         ])->onlyInput('email');
    //     }

    //     // Attempt to authenticate
    //     if (Auth::attempt($validated, $request->boolean('remember'))) {
    //         $request->session()->regenerate();

    //         // Update last login
    //         $user->update(['last_login_at' => now()]);

    //         Log::info('Login successful', [
    //             'email' => $request->email,
    //             'user_id' => $user->id,
    //             'role' => $user->role
    //         ]);

    //         // Redirect based on role
    //         return $this->redirectBasedOnRole($user);
    //     }

    //     // If authentication fails
    //     return back()->withErrors([
    //         'email' => 'Authentication failed. Please try again.',
    //     ])->onlyInput('email');
    // }

    public function login(Request $request)
    {
        Log::info('=== LOGIN START ===', ['email' => $request->email]);

        // Validate the request
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Find user by email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            Log::warning('Login failed: User not found', ['email' => $request->email]);
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        // Check password
        if (!Hash::check($request->password, $user->password)) {
            Log::warning('Login failed: Invalid password', ['email' => $request->email]);
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        // Check user status
        if ($user->status !== 'active') {
            Log::warning('Login failed: User not active', [
                'email' => $request->email,
                'status' => $user->status
            ]);
            return back()->withErrors([
                'email' => 'Your account is ' . $user->status . '. Please contact support.',
            ])->onlyInput('email');
        }

        Log::info('Before Auth::attempt', [
            'email' => $request->email,
            'remember' => $request->boolean('remember'),
            'user_exists' => true,
            'password_match' => true
        ]);

        // Attempt to authenticate
        // if (Auth::attempt($validated, $request->boolean('remember'))) {
        //     Log::info('Auth::attempt SUCCESS', [
        //         'user_id' => Auth::id(),
        //         'auth_check' => Auth::check(),
        //         'user_email' => Auth::user()->email
        //     ]);

        //     $request->session()->regenerate();

        //     Log::info('Session regenerated', [
        //         'session_id' => session()->getId(),
        //         'old_session_id' => $request->session()->get('_token')
        //     ]);

        //     // Update last login
        //     $user->update(['last_login_at' => now()]);

        //     Log::info('Login successful - about to redirect', [
        //         'email' => $request->email,
        //         'user_id' => $user->id,
        //         'role' => $user->role
        //     ]);

        //     // Redirect based on role
        //     $redirectUrl = $this->redirectBasedOnRole($user);
        //     Log::info('Redirecting to: ' . $redirectUrl->getTargetUrl());

        //     return $redirectUrl;
        // } else {
        //     Log::error('Auth::attempt FAILED', [
        //         'email' => $request->email,
        //         'auth_check' => Auth::check(),
        //         'session_id' => session()->getId()
        //     ]);
        // }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));

        // If authentication fails
        return back()->withErrors([
            'email' => 'Authentication failed. Please try again.',
        ])->onlyInput('email');
    }

    protected function redirectBasedOnRole($user)
    {
        // Use checkRole method instead of hasRole
        if ($user->checkRole('super-admin') || $user->role === 'super-admin') {
            return redirect()->intended(route('dashboard'));
        }

        // For all other authenticated users
        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    // Show registration form
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    // Handle registration
    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Create user
        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'customer', // Default role
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // Assign customer role (if using Spatie)
        if (class_exists(\Spatie\Permission\Models\Role::class)) {
            $customerRole = \Spatie\Permission\Models\Role::where('name', 'customer')->first();
            if ($customerRole) {
                $user->assignRole('customer');
            }
        }

        // Log the user in
        Auth::login($user);

        return redirect()->route('dashboard');
    }

    // Show forgot password form
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    // Send reset link email
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = \Illuminate\Support\Facades\Password::sendResetLink(
            $request->only('email')
        );

        return $status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    // Show reset password form
    public function showResetPasswordForm(Request $request, $token = null)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    // Reset password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = \Illuminate\Support\Facades\Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );

        return $status === \Illuminate\Support\Facades\Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}
