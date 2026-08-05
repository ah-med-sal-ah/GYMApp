<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ChangeAdminPasswordRequest;
use App\Http\Resources\Admin\AdminResource;
use App\Services\Admin\AdminAuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AdminAuthService $adminAuthService,
    ) {}

    public function show(Request $request): JsonResponse
    {
        return $this->success(new AdminResource($request->user()));
    }

    public function changePassword(ChangeAdminPasswordRequest $request): JsonResponse
    {
        $this->adminAuthService->changePassword(
            $request->user(),
            $request->validated('current_password'),
            $request->validated('new_password'),
        );

        return $this->success(null, 'Password changed successfully.');
    }
}
