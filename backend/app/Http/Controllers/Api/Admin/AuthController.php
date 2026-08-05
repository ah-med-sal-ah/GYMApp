<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminLoginRequest;
use App\Http\Resources\Admin\AdminResource;
use App\Services\Admin\AdminAuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AdminAuthService $adminAuthService,
    ) {}

    public function login(AdminLoginRequest $request): JsonResponse
    {
        [$admin, $token] = $this->adminAuthService->login($request->validated());

        return $this->success([
            'admin' => new AdminResource($admin),
            'token' => $token,
        ], 'Logged in successfully.');
    }
}
