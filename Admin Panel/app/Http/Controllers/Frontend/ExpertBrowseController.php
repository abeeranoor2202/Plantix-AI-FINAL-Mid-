<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Expert;
use App\Services\Shared\AppointmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ExpertBrowseController — customer-facing expert discovery & quick booking.
 *
 * Routes:
 *   GET  /experts              → index()       browse & search
 *   GET  /experts/{id}         → show()        expert profile
 *   POST /experts/{id}/book    → quickBook()   quick appointment booking from profile
 */
class ExpertBrowseController extends Controller
{
    public function __construct(
        private readonly AppointmentService $appointmentService,
    ) {}

    // ── Expert listing ────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = Expert::with(['user', 'profile', 'specializations'])
            ->where('status', Expert::STATUS_APPROVED)
            ->where('is_available', true);

        // Search by name or specialty
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"))
                  ->orWhere('specialty', 'like', "%{$search}%")
                  ->orWhere('bio', 'like', "%{$search}%")
                  ->orWhereHas('specializations', fn ($s) => $s->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('profile', fn ($p) => $p->where('specialization', 'like', "%{$search}%"));
            });
        }

        // Filter by specialization tag
        if ($spec = $request->input('specialization')) {
            $query->where(function ($q) use ($spec) {
                $q->where('specialty', 'like', "%{$spec}%")
                  ->orWhereHas('specializations', fn ($s) => $s->where('name', 'like', "%{$spec}%"));
            });
        }

        // Sort options
        $sort = $request->input('sort', 'rating');
        match ($sort) {
            'price_asc'  => $query->orderBy('consultation_price', 'asc'),
            'price_desc' => $query->orderBy('consultation_price', 'desc'),
            'experience' => $query->whereHas('profile')->orderByRaw(
                '(SELECT experience_years FROM expert_profiles WHERE expert_profiles.expert_id = experts.id) DESC'
            ),
            default      => $query->orderBy('rating_avg', 'desc'),
        };

        $experts = $query->paginate(12)->withQueryString();

        // Distinct specialization tags for filter pills
        $specializations = \App\Models\ExpertSpecialization::select('name')
            ->distinct()
            ->orderBy('name')
            ->pluck('name');

        return view('customer.experts.index', compact('experts', 'specializations', 'search', 'sort'));
    }

    // ── Expert profile ────────────────────────────────────────────────────────

    public function show(int $id): View
    {
        $expert = Expert::with([
                'user',
                'profile',
                'specializations',
                'appointments' => fn ($q) => $q->where('status', 'completed')
                    ->with('user')
                    ->latest()
                    ->limit(5),
            ])
            ->where('status', Expert::STATUS_APPROVED)
            ->findOrFail($id);

        // Completed appointment count for social proof
        $completedCount = $expert->appointments()->where('status', 'completed')->count();

        return view('customer.experts.show', compact('expert', 'completedCount'));
    }

    // ── Quick booking from profile page ───────────────────────────────────────

    public function quickBook(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $request->validate([
            'slot_id'      => 'required|exists:appointment_slots,id',
            'topic'        => 'required|string|max:200',
            'notes'        => 'nullable|string|max:500',
        ]);

        $user   = auth('web')->user();
        $expert = Expert::where('status', Expert::STATUS_APPROVED)
            ->where('is_available', true)
            ->findOrFail($id);

        $booking = $this->appointmentService->initiateBooking($user, [
            'expert_id'    => $expert->id,
            'slot_id'      => (int) $request->input('slot_id'),
            'type'         => 'online',
            'topic'        => $request->topic,
            'notes'        => $request->notes,
        ]);

        $checkoutUrl = $booking['checkout_url'] ?? route('appointments');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'redirect_url' => $checkoutUrl,
            ]);
        }

        return redirect()->away($checkoutUrl);
    }
}
