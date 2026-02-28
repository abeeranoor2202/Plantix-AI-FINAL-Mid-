<?php

namespace App\Http\Controllers\Frontend\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

/**
 * CustomerVerificationController
 *
 * Handles email address verification for customers.
 * Works with Laravel's built-in email verification system.
 *
 * Routes:
 *   GET  /email/verify/{id}/{hash}   → verify()   [signed URL]
 *   POST /email/verification-notification → resend()
 */
class CustomerVerificationController extends Controller
{
    /**
     * Handle email verification from the signed link.
     */
    public function verify(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('home')->with('info', 'Email already verified.');
        }

        $request->fulfill();

        return redirect()->route('home')->with('success', 'Your email address has been verified!');
    }

    /**
     * Resend the verification notification.
     */
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('home')->with('info', 'Email already verified.');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'A fresh verification link has been sent to your email address.');
    }
}
