<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\Api\V1\DashboardApiService;
use Illuminate\Http\Request;

class DashboardController extends ApiController
{
    public function __construct(private readonly DashboardApiService $service) {}

    public function summary(Request $request)
    {
        return $this->ok($this->service->summary($request->user()));
    }
}
