<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Appointment;
use App\Services\Api\V1\AppointmentApiService;
use Illuminate\Http\Request;

class AppointmentController extends ApiController
{
    public function __construct(private readonly AppointmentApiService $service) {}

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'type' => ['nullable', 'in:online,physical'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'page' => ['nullable', 'integer', 'min:1'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $paginator = $this->service->listForActor($request->user(), $filters, (int) ($filters['limit'] ?? 20));

        return $this->paginated($paginator, $paginator->items());
    }

    public function show(Request $request, int $id)
    {
        $actor = $request->user();
        $query = Appointment::query()->with(['user:id,name,email', 'expert.user:id,name,email']);

        if ($actor->role === 'admin') {
            $appointment = $query->findOrFail($id);
        } elseif ($actor->role === 'expert' || $actor->role === 'agency_expert') {
            $appointment = $query->where('expert_id', (int) optional($actor->expert)->id)->findOrFail($id);
        } else {
            $appointment = $query->where('user_id', $actor->id)->findOrFail($id);
        }

        return $this->ok($appointment);
    }
}
