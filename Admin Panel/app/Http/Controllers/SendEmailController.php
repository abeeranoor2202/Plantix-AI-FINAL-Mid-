<?php

namespace App\Http\Controllers;

use App\Mail\SetEmailData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SendEmailController extends Controller
{
    public function __construct()
    {
    }


    public function sendMail(Request $request)
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:20000'],
            'recipients' => ['required'],
            'recipients.*' => ['nullable'],
        ]);

        $recipients = collect((array) $data['recipients'])
            ->flatMap(fn ($item) => is_string($item) ? preg_split('/[,;\s]+/', $item) ?: [] : [])
            ->map(fn ($email) => trim((string) $email))
            ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values();

        if ($recipients->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No valid recipient email addresses were provided.',
            ], 422);
        }

        Mail::to($recipients->all())->send(new SetEmailData($data['subject'], $data['message']));

        return response()->json([
            'success' => true,
            'message' => 'Email queued successfully.',
            'recipients' => $recipients->count(),
        ]);
    }
}