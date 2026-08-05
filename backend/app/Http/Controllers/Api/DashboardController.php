<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return $this->success($this->dashboardService->summary($request->user()));
    }

    public function registrationsChart(Request $request): JsonResponse
    {
        return $this->success($this->dashboardService->registrationsChart($request->user()));
    }

    public function incomeBySportChart(Request $request): JsonResponse
    {
        return $this->success($this->dashboardService->incomeBySportChart($request->user()));
    }
}
