<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterGymRequest;
use App\Http\Resources\GymResource;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AuthService $authService,
    ) {}

    public function register(RegisterGymRequest $request): JsonResponse
    {
        [$gym, $token] = $this->authService->register($request->validated());

        return $this->success([
            'gym' => new GymResource($gym),
            'token' => $token,
        ], 'Gym registered successfully.', 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        [$gym, $token] = $this->authService->login($request->validated());

        return $this->success([
            'gym' => new GymResource($gym),
            'token' => $token,
        ], 'Logged in successfully.');
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->success(null, 'Logged out successfully.');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success(new GymResource($request->user()));
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->authService->changePassword(
            $request->user(),
            $request->validated('current_password'),
            $request->validated('password'),
        );

        return $this->success(null, 'Password changed successfully.');
    }
}
