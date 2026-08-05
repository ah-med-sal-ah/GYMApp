<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Coach;
use App\Models\Expense;
use App\Models\Gym;
use App\Models\Sport;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_returns_gym_name_and_total_clients(): void
    {
        $gym = Gym::factory()->create(['name' => 'Iron Gym']);
        Client::factory()->count(3)->for($gym)->create();

        $otherGym = Gym::factory()->create();
        Client::factory()->count(5)->for($otherGym)->create();

        $response = $this->actingAs($gym, 'sanctum')->getJson('/api/dashboard');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.gym_name', 'Iron Gym')
            ->assertJsonPath('data.total_clients', 3);
    }

    public function test_dashboard_financial_summary_is_calculated_dynamically(): void
    {
        Carbon::setTestNow('2026-06-15');

        $gym = Gym::factory()->create();

        $fitness = Sport::factory()->for($gym)->create([
            'name' => 'Fitness',
            'day_price' => 8,
            'week_price' => 10,
            'month_price' => 55,
            'year_price' => 500,
        ]);
        $swimming = Sport::factory()->for($gym)->create([
            'name' => 'Swimming',
            'day_price' => 12,
            'week_price' => 15,
            'month_price' => 70,
            'year_price' => 700,
        ]);

        // Active client: month registration, practices Fitness + Swimming -> 55 + 70 = 125
        $clientA = Client::factory()->for($gym)->create([
            'registration_type' => 'month',
            'registration_start' => Carbon::parse('2026-06-01'),
            'registration_end' => Carbon::parse('2026-07-01'),
        ]);
        $clientA->sports()->sync([$fitness->id, $swimming->id]);

        // Active client: week registration, practices Fitness -> 10
        $clientB = Client::factory()->for($gym)->create([
            'registration_type' => 'week',
            'registration_start' => Carbon::parse('2026-06-10'),
            'registration_end' => Carbon::parse('2026-06-17'),
        ]);
        $clientB->sports()->sync([$fitness->id]);

        // Expired client: must NOT generate income
        $clientC = Client::factory()->for($gym)->create([
            'registration_type' => 'month',
            'registration_start' => Carbon::parse('2026-04-01'),
            'registration_end' => Carbon::parse('2026-05-01'),
        ]);
        $clientC->sports()->sync([$fitness->id]);

        Coach::factory()->for($gym)->create(['salary' => 1000]);
        Coach::factory()->for($gym)->create(['salary' => 2000]);

        Expense::factory()->for($gym)->create(['amount' => 500]);
        Expense::factory()->for($gym)->create(['amount' => 300]);

        $response = $this->actingAs($gym, 'sanctum')->getJson('/api/dashboard');

        $response->assertOk()
            ->assertJsonPath('data.total_clients', 3)
            ->assertJsonPath('data.total_coaches', 2)
            ->assertJsonPath('data.coach_salaries', 3000)
            ->assertJsonPath('data.total_income', 135)
            ->assertJsonPath('data.total_expenses', 800)
            ->assertJsonPath('data.net_profit', 135 - 800 - 3000);

        Carbon::setTestNow();
    }

    public function test_dashboard_is_scoped_to_the_authenticated_gym(): void
    {
        $gym = Gym::factory()->create();
        $otherGym = Gym::factory()->create();

        Coach::factory()->for($otherGym)->create(['salary' => 9999]);
        Expense::factory()->for($otherGym)->create(['amount' => 9999]);

        $response = $this->actingAs($gym, 'sanctum')->getJson('/api/dashboard');

        $response->assertOk()
            ->assertJsonPath('data.total_coaches', 0)
            ->assertJsonPath('data.coach_salaries', 0)
            ->assertJsonPath('data.total_expenses', 0);
    }

    public function test_registrations_pie_chart_returns_counts_and_percentages(): void
    {
        $gym = Gym::factory()->create();

        Client::factory()->count(2)->for($gym)->create(['registration_type' => 'month']);
        Client::factory()->count(1)->for($gym)->create(['registration_type' => 'week']);

        $response = $this->actingAs($gym, 'sanctum')->getJson('/api/dashboard/charts/registrations');

        $response->assertOk()->assertJsonPath('success', true);

        $data = collect($response->json('data'))->keyBy('type');

        $this->assertSame(2, $data['month']['count']);
        $this->assertSame(1, $data['week']['count']);
        $this->assertSame(0, $data['day']['count']);
        $this->assertSame(0, $data['year']['count']);
        $this->assertEqualsWithDelta(66.7, $data['month']['percentage'], 0.1);
        $this->assertEqualsWithDelta(33.3, $data['week']['percentage'], 0.1);
        $this->assertEquals(0, $data['day']['percentage']);
        $this->assertEquals(0, $data['year']['percentage']);
    }

    public function test_income_by_sport_chart_returns_every_sport(): void
    {
        Carbon::setTestNow('2026-06-15');

        $gym = Gym::factory()->create();

        $fitness = Sport::factory()->for($gym)->create([
            'name' => 'Fitness',
            'day_price' => 10,
            'month_price' => 55,
            'year_price' => 500,
        ]);
        $swimming = Sport::factory()->for($gym)->create([
            'name' => 'Swimming',
            'day_price' => 15,
            'month_price' => 70,
            'year_price' => 700,
        ]);
        $emptySport = Sport::factory()->for($gym)->create(['name' => 'CrossFit']);

        $client = Client::factory()->for($gym)->create([
            'registration_type' => 'month',
            'registration_start' => Carbon::parse('2026-06-01'),
            'registration_end' => Carbon::parse('2026-07-01'),
        ]);
        $client->sports()->sync([$fitness->id, $swimming->id]);

        $response = $this->actingAs($gym, 'sanctum')->getJson('/api/dashboard/charts/income-by-sport');

        $response->assertOk()->assertJsonPath('success', true)->assertJsonCount(3, 'data');

        $data = collect($response->json('data'))->keyBy('sport');

        $this->assertEquals(55, $data['Fitness']['income']);
        $this->assertEquals(70, $data['Swimming']['income']);
        $this->assertEquals(0, $data[$emptySport->name]['income']);

        Carbon::setTestNow();
    }

    public function test_guest_cannot_access_dashboard_routes(): void
    {
        $this->getJson('/api/dashboard')->assertStatus(401);
        $this->getJson('/api/dashboard/charts/registrations')->assertStatus(401);
        $this->getJson('/api/dashboard/charts/income-by-sport')->assertStatus(401);
    }
}
