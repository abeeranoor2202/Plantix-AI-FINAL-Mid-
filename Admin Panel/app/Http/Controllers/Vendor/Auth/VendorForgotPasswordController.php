<?php

namespace App\Http\Controllers\Vendor\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

/**
 * VendorForgotPasswordController
 *
 * Sends a password-reset link to a vendor's email address.
 * Uses the 'vendors' password broker defined in config/auth.php.
 */
class VendorForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    public function __construct()
    {
        $this->middleware('guest:vendor');
    }

    public function showLinkRequestForm(): View
    {
        return view('vendor.auth.password-forgot');
    }

    /**
     * Override to block reset emails for disabled accounts.
     */
    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $this->validateEmail($request);

        $user = User::where('email', strtolower(trim($request->email)))
                    ->where('role', 'vendor')
                    ->first();

        if ($user && ! $user->active) {
            return $this->sendResetLinkResponse($request, Password::RESET_LINK_SENT);
        }

        $response = $this->broker()->sendResetLink(
            $this->credentials($request)
        );

        return $response === Password::RESET_LINK_SENT
            ? $this->sendResetLinkResponse($request, $response)
            : $this->sendResetLinkFailedResponse($request, $response);
    }

    protected function broker(): \Illuminate\Auth\Passwords\PasswordBroker
    {
        return Password::broker('vendors_users');
    }
}
