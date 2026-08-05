<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExpenseIndexRequest;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Http\Resources\ExpenseListResource;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use App\Services\ExpenseService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ExpenseService $expenseService,
    ) {}

    public function index(ExpenseIndexRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Expense::class);

        $expenses = $this->expenseService->list($request->user(), $request->validated());

        return $this->success([
            'expenses' => ExpenseListResource::collection($expenses->items()),
            'pagination' => [
                'current_page' => $expenses->currentPage(),
                'per_page' => $expenses->perPage(),
                'total' => $expenses->total(),
                'last_page' => $expenses->lastPage(),
            ],
        ]);
    }

    public function store(StoreExpenseRequest $request): JsonResponse
    {
        $this->authorize('create', Expense::class);

        $expense = $this->expenseService->create($request->user(), $request->validated());

        return $this->success(new ExpenseResource($expense), 'Expense created successfully.', 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $expense = $this->expenseService->find($request->user(), (int) $id);

        $this->authorize('view', $expense);

        return $this->success(new ExpenseResource($expense));
    }

    public function update(UpdateExpenseRequest $request, string $id): JsonResponse
    {
        $expense = $this->expenseService->find($request->user(), (int) $id);

        $this->authorize('update', $expense);

        $expense = $this->expenseService->update($expense, $request->validated());

        return $this->success(new ExpenseResource($expense), 'Expense updated successfully.');
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $expense = $this->expenseService->find($request->user(), (int) $id);

        $this->authorize('delete', $expense);

        $this->expenseService->delete($expense);

        return $this->success(null, 'Expense deleted successfully.');
    }
}
