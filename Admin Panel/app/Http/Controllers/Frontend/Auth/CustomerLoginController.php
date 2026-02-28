<?php

namespace App\Http\Controllers\Frontend\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CustomerLoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:web')->except(['logout']);
    }

    public function showLoginForm(): View
    {
        return view('customer.signin');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Normalize email before attempting auth
        $credentials = [
            'email'    => strtolower(trim($request->input('email'))),
            'password' => $request->input('password'),
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $user = Auth::user();

        if (! in_array($user->role, ['user'])) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'This login is for customers only.',
            ]);
        }

        if (! $user->active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'Your account has been disabled.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('signin');
    }
}
