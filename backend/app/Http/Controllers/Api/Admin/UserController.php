<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserIndexRequest;
use App\Http\Resources\Admin\UserListResource;
use App\Http\Resources\Admin\UserResource;
use App\Models\Gym;
use App\Services\Admin\AdminUserService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AdminUserService $adminUserService,
    ) {}

    public function index(UserIndexRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Gym::class);

        $gyms = $this->adminUserService->list($request->validated());

        return $this->success([
            'users' => UserListResource::collection($gyms->items()),
            'pagination' => [
                'current_page' => $gyms->currentPage(),
                'per_page' => $gyms->perPage(),
                'total' => $gyms->total(),
                'last_page' => $gyms->lastPage(),
            ],
        ]);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $gym = $this->adminUserService->find((int) $id);

        $this->authorize('view', $gym);

        return $this->success(new UserResource($gym));
    }
}
