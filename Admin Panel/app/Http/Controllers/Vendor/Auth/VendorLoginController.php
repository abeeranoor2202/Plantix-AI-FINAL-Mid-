<?php

namespace App\Http\Controllers\Vendor\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * VendorLoginController
 *
 * Authenticates vendor users via the 'vendor' guard.
 */
class VendorLoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/vendor/dashboard';

    public function __construct()
    {
        $this->middleware('guest:vendor')->except('logout');
    }

    public function showLoginForm(): View
    {
        return view('vendor.auth.login');
    }

    protected function guard(): \Illuminate\Auth\SessionGuard
    {
        return auth()->guard('vendor');
    }

    protected function authenticated(Request $request, $user): RedirectResponse
    {
        if ($user->role !== 'vendor') {
            $this->guard()->logout();
            throw ValidationException::withMessages([
                $this->username() => ['This login is for vendors only.'],
            ]);
        }

        if (! $user->active || ($user->status ?? 'active') !== 'active') {
            $this->guard()->logout();
            throw ValidationException::withMessages([
                $this->username() => ['Your vendor account is not yet active. Contact admin.'],
            ]);
        }

        // Block suspended vendors (vendors.is_active = false) even when users.active = 1
        if ($user->vendor && (! $user->vendor->is_active || ($user->vendor->status ?? 'pending') !== 'approved')) {
            $this->guard()->logout();
            throw ValidationException::withMessages([
                $this->username() => ['Your vendor account has been suspended. Please contact the admin.'],
            ]);
        }

        return redirect()->intended($this->redirectPath());
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('vendor.login');
    }
}
