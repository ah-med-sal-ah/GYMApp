<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SportIndexRequest;
use App\Http\Requests\StoreSportRequest;
use App\Http\Requests\UpdateSportRequest;
use App\Http\Resources\SportListResource;
use App\Http\Resources\SportResource;
use App\Models\Sport;
use App\Services\SportService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SportController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly SportService $sportService,
    ) {}

    public function index(SportIndexRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Sport::class);

        $sports = $this->sportService->list($request->user(), $request->validated());

        return $this->success([
            'sports' => SportListResource::collection($sports->items()),
            'pagination' => [
                'current_page' => $sports->currentPage(),
                'per_page' => $sports->perPage(),
                'total' => $sports->total(),
                'last_page' => $sports->lastPage(),
            ],
        ]);
    }

    public function store(StoreSportRequest $request): JsonResponse
    {
        $this->authorize('create', Sport::class);

        $sport = $this->sportService->create($request->user(), $request->validated(), $request->file('photo'));

        return $this->success(new SportResource($sport), 'Sport created successfully.', 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $sport = $this->sportService->find($request->user(), (int) $id);

        $this->authorize('view', $sport);

        return $this->success(new SportResource($sport));
    }

    public function update(UpdateSportRequest $request, string $id): JsonResponse
    {
        $sport = $this->sportService->find($request->user(), (int) $id);

        $this->authorize('update', $sport);

        $sport = $this->sportService->update($sport, $request->validated(), $request->file('photo'));

        return $this->success(new SportResource($sport), 'Sport updated successfully.');
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $sport = $this->sportService->find($request->user(), (int) $id);

        $this->authorize('delete', $sport);

        $this->sportService->delete($sport);

        return $this->success(null, 'Sport deleted successfully.');
    }
}
