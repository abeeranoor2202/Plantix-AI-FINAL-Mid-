<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Expert;
use App\Models\User;
use App\Services\Shared\AppointmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Schema;

class AdminAppointmentController extends Controller
{
    private const UI_STATUSES = ['pending', 'confirmed', 'completed', 'cancelled'];
    private ?array $appointmentColumns = null;

    public function __construct(
        private readonly AppointmentService $service,
    ) {}

    // ── Listing ───────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = Appointment::with(['user', 'expert.user', 'expert.profile'])->latest();

        if ($request->filled('status')) {
            $statusFilter = $request->string('status')->toString();
            $query->whereIn('status', $this->backendStatusesForUi($statusFilter));
        }
        if ($request->filled('type')) {
            $type = $request->string('type')->toString() === 'offline' ? 'physical' : 'online';
            $query->where('type', $type);
        }
        if ($request->filled('expert_id')) {
            $query->where('expert_id', $request->expert_id);
        }
        if ($request->filled('date')) {
            $query->whereDate('scheduled_at', $request->date);
        }
        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->whereHas('user', fn ($u) => $u->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term))
                  ->orWhereHas('expert.user', fn ($u) => $u->where('name', 'like', $term));
            });
        }

        $appointments = $query->paginate(20)->withQueryString();
        $experts      = Expert::with('user')->available()->get();
        $statuses     = self::UI_STATUSES;

        return view('admin.appointments.index', compact('appointments', 'experts', 'statuses'));
    }

    public function create(): View
    {
        $experts = Expert::with('user')->available()->get();
        $customers = User::where('role', 'user')->orderBy('name')->limit(200)->get(['id', 'name', 'email']);
        $statuses = self::UI_STATUSES;

        return view('admin.appointments.create', compact('experts', 'customers', 'statuses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'expert_id'             => 'required|exists:experts,id',
            'type'                  => ['required', Rule::in(['online', 'offline'])],
            'scheduled_at'          => 'required|date_format:Y-m-d\TH:i',
            'fee'                   => 'nullable|numeric|min:0',
            'topic'                 => 'nullable|string|max:255',
            'admin_notes'           => 'nullable|string|max:1000',
            'status'                => ['required', Rule::in(self::UI_STATUSES)],
            'payment_status'        => ['required', Rule::in(['unpaid', 'paid', 'refunded'])],
            'notifications_enabled' => 'nullable|boolean',

            'meeting_link'          => 'required_if:type,online|nullable|url|max:500',
            'platform'              => 'nullable|string|max:60',

            'venue_name'            => 'required_if:type,offline|nullable|string|max:150',
            'address_line1'         => 'required_if:type,offline|nullable|string|max:255',
            'address_line2'         => 'nullable|string|max:255',
            'city'                  => 'required_if:type,offline|nullable|string|max:100',

            'user_id'               => 'nullable|exists:users,id',
            'quick_first_name'      => 'required_without:user_id|nullable|string|max:100',
            'quick_last_name'       => 'required_without:user_id|nullable|string|max:100',
            'quick_email'           => 'required_without:user_id|nullable|email|max:255|unique:users,email',
            'quick_phone'           => 'required_without:user_id|nullable|string|max:30',
        ]);

        $userId = $request->integer('user_id');
        if (! $userId) {
            $quickCustomer = User::create([
                'name'              => trim($request->string('quick_first_name')->toString() . ' ' . $request->string('quick_last_name')->toString()),
                'email'             => $request->string('quick_email')->toString(),
                'phone'             => $request->string('quick_phone')->toString(),
                'password'          => bcrypt('User@123456'),
                'role'              => 'user',
                'active'            => 1,
                'email_verified_at' => now(),
            ]);

            $userId = $quickCustomer->id;
        }

        $type = $request->string('type')->toString() === 'offline' ? 'physical' : 'online';
        $uiStatus = $request->string('status')->toString();
        $scheduledAt = $request->date('scheduled_at');

        $this->assertNoScheduleConflict(
            $request->integer('expert_id'),
            $scheduledAt,
            null
        );

        $appointment = Appointment::create($this->persistable([
            'user_id'               => $userId,
            'expert_id'             => $request->integer('expert_id'),
            'admin_id'              => auth('admin')->id(),
            'type'                  => $type,
            'scheduled_at'          => $scheduledAt,
            'duration_minutes'      => 60,
            'fee'                   => $request->input('fee', 0),
            'topic'                 => $request->input('topic'),
            'notes'                 => null,
            'admin_notes'           => $request->input('admin_notes'),
            'meeting_link'          => $type === 'online' ? $request->input('meeting_link') : null,
            'platform'              => $type === 'online' ? $request->input('platform') : null,
            'venue_name'            => $type === 'physical' ? $request->input('venue_name') : null,
            'address_line1'         => $type === 'physical' ? $request->input('address_line1') : null,
            'address_line2'         => $type === 'physical' ? $request->input('address_line2') : null,
            'city'                  => $type === 'physical' ? $request->input('city') : null,
            'payment_status'        => $request->input('payment_status'),
            'notifications_enabled' => $request->boolean('notifications_enabled', true),
            'status'                => $this->dbStatusFromUi($uiStatus),
        ]));

        return redirect()->route('admin.appointments.show', $appointment->id)
            ->with('success', 'Appointment created successfully.');
    }

    public function show(int $id): View
    {
        $appointment = Appointment::with(['user', 'expert.user', 'expert.profile', 'statusHistory', 'logs', 'reschedules'])
            ->findOrFail($id);
        $experts = Expert::with('user')->available()->get();

        return view('admin.appointments.show', compact('appointment', 'experts'));
    }

    // ── Edit functionality ────────────────────────────────────────────────────

    public function edit(int $id): View
    {
        $appointment = Appointment::with(['user', 'expert.user', 'expert.profile'])
            ->findOrFail($id);
        $experts = Expert::with('user')->available()->get();

        return view('admin.appointments.edit', compact('appointment', 'experts'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $appointment = Appointment::findOrFail($id);

        $request->validate([
            'expert_id'             => 'required|exists:experts,id',
            'type'                  => ['required', Rule::in(['online', 'offline'])],
            'scheduled_at'          => 'required|date_format:Y-m-d\TH:i',
            'fee'                   => 'nullable|numeric|min:0',
            'topic'                 => 'nullable|string|max:255',
            'admin_notes'           => 'nullable|string|max:1000',
            'status'                => ['required', Rule::in(self::UI_STATUSES)],
            'payment_status'        => ['required', Rule::in(['unpaid', 'paid', 'refunded'])],
            'notifications_enabled' => 'nullable|boolean',

            'meeting_link'          => 'required_if:type,online|nullable|url|max:500',
            'platform'              => 'nullable|string|max:60',

            'venue_name'            => 'required_if:type,offline|nullable|string|max:150',
            'address_line1'         => 'required_if:type,offline|nullable|string|max:255',
            'address_line2'         => 'nullable|string|max:255',
            'city'                  => 'required_if:type,offline|nullable|string|max:100',
        ]);

        $type = $request->string('type')->toString() === 'offline' ? 'physical' : 'online';
        $scheduledAt = $request->date('scheduled_at');

        $this->assertNoScheduleConflict(
            $request->integer('expert_id'),
            $scheduledAt,
            $appointment->id
        );

        $appointment->update($this->persistable([
            'expert_id'             => $request->integer('expert_id'),
            'type'                  => $type,
            'scheduled_at'          => $scheduledAt,
            'fee'                   => $request->input('fee', 0),
            'topic'                 => $request->input('topic'),
            'admin_notes'           => $request->input('admin_notes'),
            'meeting_link'          => $type === 'online' ? $request->input('meeting_link') : null,
            'platform'              => $type === 'online' ? $request->input('platform') : null,
            'venue_name'            => $type === 'physical' ? $request->input('venue_name') : null,
            'address_line1'         => $type === 'physical' ? $request->input('address_line1') : null,
            'address_line2'         => $type === 'physical' ? $request->input('address_line2') : null,
            'city'                  => $type === 'physical' ? $request->input('city') : null,
            'payment_status'        => $request->input('payment_status'),
            'notifications_enabled' => $request->boolean('notifications_enabled', true),
            'status'                => $this->dbStatusFromUi($request->string('status')->toString()),
        ]));

        return redirect()->route('admin.appointments.show', $appointment->id)
            ->with('success', 'Appointment updated successfully.');
    }

    private function dbStatusFromUi(string $status): string
    {
        return match ($status) {
            'pending' => Appointment::STATUS_PENDING_EXPERT_APPROVAL,
            'confirmed' => Appointment::STATUS_CONFIRMED,
            'completed' => Appointment::STATUS_COMPLETED,
            'cancelled' => Appointment::STATUS_CANCELLED,
            default => Appointment::STATUS_PENDING_EXPERT_APPROVAL,
        };
    }

    /**
     * @return array<int, string>
     */
    private function backendStatusesForUi(string $status): array
    {
        return match ($status) {
            'pending' => [
                Appointment::STATUS_DRAFT,
                Appointment::STATUS_PENDING_PAYMENT,
                Appointment::STATUS_PAYMENT_FAILED,
                Appointment::STATUS_PENDING_EXPERT_APPROVAL,
                Appointment::STATUS_RESCHEDULE_REQUESTED,
                Appointment::STATUS_PENDING,
                Appointment::STATUS_REQUESTED,
                Appointment::STATUS_RESCHEDULED,
            ],
            'confirmed' => [Appointment::STATUS_CONFIRMED, Appointment::STATUS_ACCEPTED],
            'completed' => [Appointment::STATUS_COMPLETED],
            'cancelled' => [Appointment::STATUS_CANCELLED, Appointment::STATUS_REJECTED],
            default => [
                Appointment::STATUS_DRAFT,
                Appointment::STATUS_PENDING_PAYMENT,
                Appointment::STATUS_PAYMENT_FAILED,
                Appointment::STATUS_PENDING_EXPERT_APPROVAL,
                Appointment::STATUS_CONFIRMED,
                Appointment::STATUS_COMPLETED,
                Appointment::STATUS_CANCELLED,
                Appointment::STATUS_REJECTED,
                Appointment::STATUS_RESCHEDULE_REQUESTED,
                Appointment::STATUS_PENDING,
                Appointment::STATUS_REQUESTED,
                Appointment::STATUS_ACCEPTED,
                Appointment::STATUS_RESCHEDULED,
            ],
        };
    }

    /**
     * Filter payload so only existing appointments table columns are written.
     */
    private function persistable(array $payload): array
    {
        if ($this->appointmentColumns === null) {
            $this->appointmentColumns = Schema::getColumnListing('appointments');
        }

        return array_filter(
            $payload,
            fn ($value, $key) => in_array($key, $this->appointmentColumns, true),
            ARRAY_FILTER_USE_BOTH
        );
    }

    private function assertNoScheduleConflict(int $expertId, ?\Carbon\Carbon $scheduledAt, ?int $ignoreAppointmentId = null): void
    {
        if (! $expertId || ! $scheduledAt) {
            return;
        }

        $query = Appointment::query()
            ->where('expert_id', $expertId)
            ->where('scheduled_at', $scheduledAt)
            ->whereNotIn('status', [
                Appointment::STATUS_CANCELLED,
                Appointment::STATUS_REJECTED,
                Appointment::STATUS_PAYMENT_FAILED,
            ]);

        if ($ignoreAppointmentId) {
            $query->where('id', '!=', $ignoreAppointmentId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'scheduled_at' => 'This expert already has an appointment at the selected date/time.',
            ]);
        }
    }

    // ── Delete functionality ──────────────────────────────────────────────────

    public function destroy(int $id): RedirectResponse
    {
        $appointment = Appointment::findOrFail($id);

        // Prevent deletion of completed appointments
        if ($appointment->status === Appointment::STATUS_COMPLETED) {
            return back()->withErrors(['error' => 'Cannot delete completed appointments. Please refund and cancel instead.']);
        }

        $appointment->delete();

        return redirect()->route('admin.appointments.index')
            ->with('success', 'Appointment deleted successfully.');
    }

    // ── Status transitions ────────────────────────────────────────────────────

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $appointment = Appointment::findOrFail($id);

        $allowedStatuses = array_values(array_unique(array_merge(
            Appointment::TRANSITIONS[$appointment->status] ?? [],
            $appointment->status !== Appointment::STATUS_CANCELLED ? [Appointment::STATUS_CANCELLED] : []
        )));

        if (empty($allowedStatuses)) {
            return back()->withErrors(['error' => 'No further status changes are available for this appointment.']);
        }

        $request->validate([
            'status' => ['required', Rule::in($allowedStatuses)],
            'notes'  => 'nullable|string|max:500',
        ]);

        try {
            $this->service->updateStatus(
                $appointment,
                $request->string('status')->toString(),
                auth('admin')->id(),
                $request->notes
            );
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Appointment status updated successfully.');
    }

    /**
     * Admin force-confirms an appointment (bypasses state machine's normal path).
     */
    public function confirm(Request $request, int $id): RedirectResponse
    {
        $appointment = Appointment::findOrFail($id);

        $request->validate([
            'expert_id' => 'nullable|exists:experts,id',
            'meeting_link' => ($appointment->type === 'online' ? 'required' : 'nullable') . '|url|max:500',
            'notes'     => 'nullable|string|max:1000',
        ]);

        try {
            $this->service->confirm(
                $appointment,
                $request->expert_id,
                $request->notes,
                true,
                auth('admin')->id(),
                $request->input('meeting_link')
            );
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Appointment confirmed.');
    }

    public function cancel(Request $request, int $id): RedirectResponse
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $appointment = Appointment::findOrFail($id);
        $userId = auth('admin')->id();

        $this->service->cancel($appointment, $request->reason, true, $userId);

        return back()->with('success', 'Appointment cancelled.');
    }

    public function complete(Request $request, int $id): RedirectResponse
    {
        $appointment = Appointment::findOrFail($id);
        $userId = auth('admin')->id();

        try {
            $this->service->complete($appointment, $userId);
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Appointment marked as completed.');
    }

    // ── Stripe refund ─────────────────────────────────────────────────────────

    /**
     * Issue a full or partial Stripe refund from the admin panel.
     */
    public function refund(Request $request, int $id): RedirectResponse
    {
        $appointment = Appointment::findOrFail($id);

        $request->validate([
            'refund_type' => 'required|in:full,partial',
            'amount'      => 'required_if:refund_type,partial|nullable|numeric|min:0.01|max:' . ($appointment->fee ?? 0),
            'reason'      => 'required|string|max:500',
        ]);

        try {
            $this->service->adminRefund(
                $appointment,
                $request->refund_type === 'full' ? null : (float) $request->amount,
                $request->reason,
                auth('admin')->id()
            );
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return back()->withErrors(['error' => 'Stripe refund failed: ' . $e->getMessage()]);
        }

        return back()->with('success', 'Refund issued successfully.');
    }

    // ── Reassign expert ───────────────────────────────────────────────────────

    public function reassign(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'expert_id' => 'required|exists:experts,id',
            'reason'    => 'required|string|max:500',
        ]);

        $appointment = Appointment::findOrFail($id);

        try {
            $this->service->reassignExpert(
                $appointment,
                (int) $request->expert_id,
                auth('admin')->id(),
                $request->reason
            );
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Expert reassigned.');
    }
}

