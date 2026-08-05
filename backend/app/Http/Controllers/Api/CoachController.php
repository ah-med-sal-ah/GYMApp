<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CoachBySportRequest;
use App\Http\Requests\CoachIndexRequest;
use App\Http\Requests\StoreCoachRequest;
use App\Http\Requests\UpdateCoachRequest;
use App\Http\Resources\CoachBySportResource;
use App\Http\Resources\CoachListResource;
use App\Http\Resources\CoachResource;
use App\Models\Coach;
use App\Services\CoachService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CoachController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly CoachService $coachService,
    ) {}

    public function index(CoachIndexRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Coach::class);

        $coaches = $this->coachService->list($request->user(), $request->validated());

        return $this->success([
            'coaches' => CoachListResource::collection($coaches->items()),
            'pagination' => [
                'current_page' => $coaches->currentPage(),
                'per_page' => $coaches->perPage(),
                'total' => $coaches->total(),
                'last_page' => $coaches->lastPage(),
            ],
        ]);
    }

    public function bySport(CoachBySportRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Coach::class);

        $coaches = $this->coachService->findBySport($request->user(), (int) $request->validated('sport_id'));

        return $this->success(CoachBySportResource::collection($coaches));
    }

    public function store(StoreCoachRequest $request): JsonResponse
    {
        $this->authorize('create', Coach::class);

        $coach = $this->coachService->create($request->user(), $request->validated(), $request->file('photo'));

        return $this->success(new CoachResource($coach), 'Coach created successfully.', 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $coach = $this->coachService->find($request->user(), (int) $id);

        $this->authorize('view', $coach);

        return $this->success(new CoachResource($coach));
    }

    public function update(UpdateCoachRequest $request, string $id): JsonResponse
    {
        $coach = $this->coachService->find($request->user(), (int) $id);

        $this->authorize('update', $coach);

        $coach = $this->coachService->update($coach, $request->validated(), $request->file('photo'));

        return $this->success(new CoachResource($coach), 'Coach updated successfully.');
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $coach = $this->coachService->find($request->user(), (int) $id);

        $this->authorize('delete', $coach);

        $this->coachService->delete($coach);

        return $this->success(null, 'Coach deleted successfully.');
    }
}
