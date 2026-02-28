<?php

namespace App\Http\Controllers\Expert\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

/**
 * ExpertForgotPasswordController
 *
 * Sends a password reset link via SMTP to the expert's registered email.
 * Uses the 'experts_users' password broker defined in config/auth.php.
 */
class ExpertForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    public function showLinkRequestForm(): View
    {
        return view('expert.auth.passwords.email');
    }

    /**
     * Override to block reset emails for disabled accounts.
     */
    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $this->validateEmail($request);

        $user = User::where('email', strtolower(trim($request->email)))
                    ->where('role', 'expert')
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
        return Password::broker('experts_users');
    }
}
