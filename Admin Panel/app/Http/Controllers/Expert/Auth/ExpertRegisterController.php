<?php

namespace App\Http\Controllers\Expert\Auth;

use App\Http\Controllers\Controller;
use App\Models\Expert;
use App\Models\ExpertApplication;
use App\Models\ExpertProfile;
use App\Models\User;
use App\Notifications\Admin\AdminNewExpertApplicationNotification;
use App\Notifications\Expert\ExpertApplicationReceivedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * ExpertRegisterController
 *
 * Handles new expert / agency registration.
 *
 * Flow:
 *   1. Validate input (including optional document uploads).
 *   2. Inside a DB transaction:
 *      a. Create User  (role = expert | agency_expert, email pre-verified)
 *      b. Create Expert (status = pending)
 *      c. Create ExpertProfile (approval_status = pending)
 *      d. Create ExpertApplication (admin review queue)
 *   3. After commit dispatch queued notifications:
 *      – ExpertApplicationReceivedNotification  → expert
 *      – AdminNewExpertApplicationNotification  → all admin users
 *   4. Redirect to pending-review page.
 *
 * The expert CANNOT log in until an admin approves their application
 * (enforced by EnsureExpertGuard + ExpertLoginController).
 */
class ExpertRegisterController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:expert');
    }

    // ── Show registration form ────────────────────────────────────────────────

    public function showRegistrationForm(): View
    {
        return view('expert.auth.register');
    }

    // ── Process registration ──────────────────────────────────────────────────

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            // Account
            'account_type'        => ['required', 'in:individual,agency'],
            'name'                => ['required', 'string', 'max:100'],
            'email'               => ['required', 'email', 'unique:users,email'],
            'phone'               => ['required', 'string', 'max:20'],
            'password'            => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'agency_name'         => ['nullable', 'required_if:account_type,agency', 'string', 'max:150'],
            // Professional
            'specialty'           => ['required', 'string', 'max:100'],
            'specialization'      => ['nullable', 'string', 'max:200'],
            'experience_years'    => ['required', 'integer', 'min:0', 'max:60'],
            'bio'                 => ['required', 'string', 'min:50', 'max:2000'],
            'city'                => ['required', 'string', 'max:80'],
            'country'             => ['required', 'string', 'max:80'],
            'certifications'      => ['nullable', 'string', 'max:1000'],
            'website'             => ['nullable', 'url', 'max:255'],
            'linkedin'            => ['nullable', 'url', 'max:255'],
            'contact_phone'       => ['nullable', 'string', 'max:20'],
            'consultation_price'  => ['nullable', 'numeric', 'min:0', 'max:99999'],
            // Document uploads (optional but recommended)
            'certifications_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'id_document'         => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        // Store uploaded files BEFORE the transaction (disk I/O outside DB lock)
        $certPath  = $request->hasFile('certifications_file')
            ? $request->file('certifications_file')->store('expert-applications/certifications', 'public')
            : null;

        $idDocPath = $request->hasFile('id_document')
            ? $request->file('id_document')->store('expert-applications/ids', 'public')
            : null;

        // ── Atomic DB writes ──────────────────────────────────────────────────
        $registeredUser = null;

        DB::transaction(function () use ($data, $certPath, $idDocPath, &$registeredUser) {

            // 1. Create User account.
            //    We mark email_verified_at = now() immediately so the expert
            //    does NOT receive a verification email (experts prove identity
            //    through the admin review process, not email link clicking).
            $user = User::create([
                'name'               => $data['name'],
                'email'              => $data['email'],
                'phone'              => $data['phone'],
                'password'           => Hash::make($data['password']),
                'role'               => $data['account_type'] === 'agency' ? 'agency_expert' : 'expert',
                'active'             => true,
                'email_verified_at'  => now(),   // pre-verify; admin review is the gate
            ]);

            // 2. Create Expert record (status = pending, not yet available)
            $expert = Expert::create([
                'user_id'            => $user->id,
                'status'             => Expert::STATUS_PENDING,
                'specialty'          => $data['specialty'],
                'bio'                => $data['bio'],
                'is_available'       => false,
                'consultation_price' => $data['consultation_price'] ?? null,
            ]);

            // 3. Create ExpertProfile (extended metadata + location)
            //    Note: bio lives on the experts table, not expert_profiles.
            ExpertProfile::create([
                'expert_id'        => $expert->id,
                'account_type'     => $data['account_type'],
                'agency_name'      => $data['agency_name'] ?? null,
                'specialization'   => $data['specialization'] ?? $data['specialty'],
                'experience_years' => (int) $data['experience_years'],
                'certifications'   => $data['certifications'] ?? null,
                'city'             => $data['city'],
                'country'          => $data['country'],
                'website'          => $data['website'] ?? null,
                'linkedin'         => $data['linkedin'] ?? null,
                'contact_phone'    => $data['contact_phone'] ?? $data['phone'],
                'approval_status'  => Expert::STATUS_PENDING,
            ]);

            // 4. Create ExpertApplication — feeds the admin review queue.
            //    This is the canonical record that admins action in the AdminExpertController.
            ExpertApplication::create([
                'user_id'             => $user->id,
                'full_name'           => $data['name'],
                'specialization'      => $data['specialization'] ?? $data['specialty'],
                'experience_years'    => (int) $data['experience_years'],
                'qualifications'      => $data['certifications'] ?? null,
                'bio'                 => $data['bio'],
                'certifications_path' => $certPath,
                'id_document_path'    => $idDocPath,
                'contact_phone'       => $data['contact_phone'] ?? $data['phone'],
                'city'                => $data['city'],
                'country'             => $data['country'],
                'website'             => $data['website'] ?? null,
                'linkedin'            => $data['linkedin'] ?? null,
                'account_type'        => $data['account_type'],
                'agency_name'         => $data['agency_name'] ?? null,
                'status'              => ExpertApplication::STATUS_PENDING,
            ]);

            $registeredUser = $user;
        });

        // ── Post-commit notifications (queued — never block the response) ─────
        try {
            // Email to applicant: "Application received, pending review"
            $registeredUser->notify(
                new ExpertApplicationReceivedNotification($registeredUser->expert)
            );
        } catch (\Throwable $e) {
            Log::warning('ExpertApplicationReceivedNotification failed', [
                'user_id' => $registeredUser->id,
                'error'   => $e->getMessage(),
            ]);
        }

        try {
            // Email to all admin users: "New expert application from X"
            $adminNotification = new AdminNewExpertApplicationNotification($registeredUser->expert);
            User::where('role', 'admin')
                ->where('active', true)
                ->get()
                ->each(fn (User $admin) => $admin->notify($adminNotification));
        } catch (\Throwable $e) {
            Log::warning('AdminNewExpertApplicationNotification failed', [
                'user_id' => $registeredUser->id,
                'error'   => $e->getMessage(),
            ]);
        }

        return redirect()
            ->route('expert.register.pending')
            ->with('registered_email', $request->email);
    }

    // ── Pending approval page ─────────────────────────────────────────────────

    public function pending(): View
    {
        return view('expert.auth.register-pending');
    }
}
