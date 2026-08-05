<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Gym;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_gym_can_create_an_expense(): void
    {
        Carbon::setTestNow('2026-01-15');

        $gym = Gym::factory()->create();

        $response = $this->actingAs($gym, 'sanctum')->postJson('/api/expenses', [
            'title' => 'New treadmill',
            'description' => 'Replacement for broken unit',
            'amount' => 1200,
            'category' => 'Equipment',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'New treadmill')
            ->assertJsonPath('data.amount', 1200)
            ->assertJsonPath('data.category', 'Equipment')
            ->assertJsonPath('data.expense_date', '2026-01-15');

        $this->assertDatabaseHas('expenses', [
            'gym_id' => $gym->id,
            'title' => 'New treadmill',
        ]);

        Carbon::setTestNow();
    }

    public function test_expense_date_can_be_explicitly_provided(): void
    {
        $gym = Gym::factory()->create();

        $response = $this->actingAs($gym, 'sanctum')->postJson('/api/expenses', [
            'title' => 'Rent',
            'amount' => 500,
            'category' => 'Rent',
            'expense_date' => '2026-02-01',
        ]);

        $response->assertCreated()->assertJsonPath('data.expense_date', '2026-02-01');
    }

    public function test_creating_an_expense_requires_title_amount_and_category(): void
    {
        $gym = Gym::factory()->create();

        $response = $this->actingAs($gym, 'sanctum')->postJson('/api/expenses', []);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['title', 'amount', 'category']);
    }

    public function test_amount_must_be_greater_than_zero(): void
    {
        $gym = Gym::factory()->create();

        $response = $this->actingAs($gym, 'sanctum')->postJson('/api/expenses', [
            'title' => 'Rent',
            'amount' => 0,
            'category' => 'Rent',
        ]);

        $response->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_category_must_be_a_known_value(): void
    {
        $gym = Gym::factory()->create();

        $response = $this->actingAs($gym, 'sanctum')->postJson('/api/expenses', [
            'title' => 'Rent',
            'amount' => 100,
            'category' => 'NotACategory',
        ]);

        $response->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_a_gym_can_list_only_its_own_expenses_paginated(): void
    {
        $gym = Gym::factory()->create();
        $otherGym = Gym::factory()->create();
        Expense::factory()->count(3)->for($gym)->create();
        Expense::factory()->count(2)->for($otherGym)->create();

        $response = $this->actingAs($gym, 'sanctum')->getJson('/api/expenses');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'data.expenses')
            ->assertJsonPath('data.pagination.total', 3);
    }

    public function test_expense_list_can_be_searched_by_title_category_or_description(): void
    {
        $gym = Gym::factory()->create();
        Expense::factory()->for($gym)->create(['title' => 'Treadmill purchase']);
        Expense::factory()->for($gym)->create(['title' => 'Water bill', 'description' => 'Monthly water usage']);

        $response = $this->actingAs($gym, 'sanctum')->getJson('/api/expenses?search=Treadmill');
        $response->assertOk()->assertJsonCount(1, 'data.expenses');

        $response = $this->actingAs($gym, 'sanctum')->getJson('/api/expenses?search=water usage');
        $response->assertOk()->assertJsonCount(1, 'data.expenses');
    }

    public function test_expense_list_can_be_filtered_by_category(): void
    {
        $gym = Gym::factory()->create();
        Expense::factory()->for($gym)->create(['category' => 'Rent']);
        Expense::factory()->for($gym)->create(['category' => 'Equipment']);

        $response = $this->actingAs($gym, 'sanctum')->getJson('/api/expenses?category=Rent');

        $response->assertOk()->assertJsonCount(1, 'data.expenses');
    }

    public function test_expense_list_can_be_sorted_by_amount(): void
    {
        $gym = Gym::factory()->create();
        Expense::factory()->for($gym)->create(['title' => 'Cheap', 'amount' => 10]);
        Expense::factory()->for($gym)->create(['title' => 'Expensive', 'amount' => 999]);

        $response = $this->actingAs($gym, 'sanctum')
            ->getJson('/api/expenses?sort_by=amount&sort_order=asc');

        $response->assertOk();
        $titles = collect($response->json('data.expenses'))->pluck('title')->all();
        $this->assertSame(['Cheap', 'Expensive'], $titles);
    }

    public function test_a_gym_can_view_its_own_expense_details(): void
    {
        $gym = Gym::factory()->create();
        $expense = Expense::factory()->for($gym)->create();

        $response = $this->actingAs($gym, 'sanctum')->getJson("/api/expenses/{$expense->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $expense->id)
            ->assertJsonStructure(['data' => [
                'id', 'title', 'description', 'amount', 'category', 'expense_date', 'created_at', 'updated_at',
            ]]);
    }

    public function test_a_gym_cannot_view_another_gyms_expense(): void
    {
        $gym = Gym::factory()->create();
        $otherGym = Gym::factory()->create();
        $expense = Expense::factory()->for($otherGym)->create();

        $response = $this->actingAs($gym, 'sanctum')->getJson("/api/expenses/{$expense->id}");

        $response->assertStatus(404);
    }

    public function test_a_gym_can_update_its_own_expense(): void
    {
        $gym = Gym::factory()->create();
        $expense = Expense::factory()->for($gym)->create();

        $response = $this->actingAs($gym, 'sanctum')->putJson("/api/expenses/{$expense->id}", [
            'title' => 'Updated title',
            'amount' => 250,
            'category' => 'Maintenance',
            'expense_date' => '2026-03-01',
        ]);

        $response->assertOk()->assertJsonPath('data.title', 'Updated title');
        $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'title' => 'Updated title']);
    }

    public function test_a_gym_cannot_update_another_gyms_expense(): void
    {
        $gym = Gym::factory()->create();
        $otherGym = Gym::factory()->create();
        $expense = Expense::factory()->for($otherGym)->create();

        $response = $this->actingAs($gym, 'sanctum')->putJson("/api/expenses/{$expense->id}", [
            'title' => 'Hacked',
            'amount' => 250,
            'category' => 'Maintenance',
            'expense_date' => '2026-03-01',
        ]);

        $response->assertStatus(404);
    }

    public function test_a_gym_can_soft_delete_its_own_expense(): void
    {
        $gym = Gym::factory()->create();
        $expense = Expense::factory()->for($gym)->create();

        $response = $this->actingAs($gym, 'sanctum')->deleteJson("/api/expenses/{$expense->id}");

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertSoftDeleted('expenses', ['id' => $expense->id]);
    }

    public function test_a_gym_cannot_delete_another_gyms_expense(): void
    {
        $gym = Gym::factory()->create();
        $otherGym = Gym::factory()->create();
        $expense = Expense::factory()->for($otherGym)->create();

        $response = $this->actingAs($gym, 'sanctum')->deleteJson("/api/expenses/{$expense->id}");

        $response->assertStatus(404);
        $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'deleted_at' => null]);
    }

    public function test_guest_cannot_access_expense_routes(): void
    {
        $this->getJson('/api/expenses')->assertStatus(401);
    }
}
