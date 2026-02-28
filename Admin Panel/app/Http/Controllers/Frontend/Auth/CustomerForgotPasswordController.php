<?php

namespace App\Http\Controllers\Frontend\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class CustomerForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    public function showLinkRequestForm(): View
    {
        return view('customer.password-forgot');
    }

    /**
     * Override to block reset emails for disabled accounts.
     * We return the same "sent" response for inactive users to prevent
     * account enumeration.
     */
    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $this->validateEmail($request);

        $user = User::where('email', strtolower(trim($request->email)))->first();

        if ($user && ! $user->active) {
            // Silently return success — do NOT send the email
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
        return Password::broker('users');
    }
}
