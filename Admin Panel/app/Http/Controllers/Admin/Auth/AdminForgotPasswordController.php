<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class AdminForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    public function showLinkRequestForm(): View
    {
        return view('admin.auth.passwords.email');
    }

    /**
     * Override to block reset emails for disabled accounts.
     */
    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $this->validateEmail($request);

        $user = User::where('email', strtolower(trim($request->email)))
                    ->whereIn('role', ['admin', 'staff'])
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
        return Password::broker('admins');
    }
}
