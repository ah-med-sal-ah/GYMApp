<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClientIndexRequest;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Http\Resources\ClientListResource;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Services\ClientService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ClientService $clientService,
    ) {}

    public function index(ClientIndexRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Client::class);

        $clients = $this->clientService->list($request->user(), $request->validated());

        return $this->success([
            'clients' => ClientListResource::collection($clients->items()),
            'pagination' => [
                'current_page' => $clients->currentPage(),
                'per_page' => $clients->perPage(),
                'total' => $clients->total(),
                'last_page' => $clients->lastPage(),
            ],
        ]);
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        $this->authorize('create', Client::class);

        $client = $this->clientService->create($request->user(), $request->validated(), $request->file('photo'));

        return $this->success(new ClientResource($client), 'Client created successfully.', 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $client = $this->clientService->find($request->user(), (int) $id);

        $this->authorize('view', $client);

        return $this->success(new ClientResource($client));
    }

    public function update(UpdateClientRequest $request, string $id): JsonResponse
    {
        $client = $this->clientService->find($request->user(), (int) $id);

        $this->authorize('update', $client);

        $client = $this->clientService->update($client, $request->validated(), $request->file('photo'));

        return $this->success(new ClientResource($client), 'Client updated successfully.');
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $client = $this->clientService->find($request->user(), (int) $id);

        $this->authorize('delete', $client);

        $this->clientService->delete($client);

        return $this->success(null, 'Client deleted successfully.');
    }
}
