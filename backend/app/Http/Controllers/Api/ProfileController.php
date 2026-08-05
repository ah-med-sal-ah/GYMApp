<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateGymProfileRequest;
use App\Http\Resources\GymResource;
use App\Services\ProfileService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ProfileService $profileService,
    ) {}

    public function show(Request $request): JsonResponse
    {
        return $this->success(new GymResource($request->user()));
    }

    public function update(UpdateGymProfileRequest $request): JsonResponse
    {
        $gym = $this->profileService->update($request->user(), $request->validated());

        return $this->success(new GymResource($gym), 'Profile updated successfully.');
    }
}
