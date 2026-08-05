<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Coach;
use App\Models\Gym;
use App\Models\Sport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SportTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_gym_can_create_a_sport(): void
    {
        $gym = Gym::factory()->create();

        $response = $this->actingAs($gym, 'sanctum')->postJson('/api/sports', [
            'name' => 'Bodybuilding',
            'day_price' => 5,
            'month_price' => 55,
            'year_price' => 550,
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Bodybuilding')
            ->assertJsonPath('data.day_price', 5)
            ->assertJsonPath('data.month_price', 55)
            ->assertJsonPath('data.year_price', 550)
            ->assertJsonPath('data.number_of_coaches', 0)
            ->assertJsonPath('data.number_of_clients', 0);

        $this->assertDatabaseHas('sports', [
            'gym_id' => $gym->id,
            'name' => 'Bodybuilding',
        ]);
    }

    public function test_creating_a_sport_requires_a_name(): void
    {
        $gym = Gym::factory()->create();

        $response = $this->actingAs($gym, 'sanctum')->postJson('/api/sports', []);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['name', 'day_price']);
    }

    public function test_creating_a_sport_requires_at_least_one_price_tier(): void
    {
        $gym = Gym::factory()->create();

        $response = $this->actingAs($gym, 'sanctum')->postJson('/api/sports', [
            'name' => 'Bodybuilding',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['day_price']);
    }

    public function test_a_sport_can_be_created_with_only_some_price_tiers(): void
    {
        $gym = Gym::factory()->create();

        $response = $this->actingAs($gym, 'sanctum')->postJson('/api/sports', [
            'name' => 'Bodybuilding',
            'day_price' => 5,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.day_price', 5)
            ->assertJsonPath('data.week_price', null)
            ->assertJsonPath('data.month_price', null)
            ->assertJsonPath('data.year_price', null);
    }

    public function test_prices_must_be_zero_or_greater(): void
    {
        $gym = Gym::factory()->create();

        $response = $this->actingAs($gym, 'sanctum')->postJson('/api/sports', [
            'name' => 'Bodybuilding',
            'day_price' => -1,
            'month_price' => -5,
            'year_price' => -10,
        ]);

        $response->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_a_gym_cannot_have_two_sports_with_the_same_name(): void
    {
        $gym = Gym::factory()->create();
        Sport::factory()->for($gym)->create(['name' => 'Bodybuilding']);

        $response = $this->actingAs($gym, 'sanctum')->postJson('/api/sports', [
            'name' => 'Bodybuilding',
            'day_price' => 5,
            'month_price' => 55,
            'year_price' => 550,
        ]);

        $response->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_same_sport_name_is_allowed_across_different_gyms(): void
    {
        $gymA = Gym::factory()->create();
        $gymB = Gym::factory()->create();
        Sport::factory()->for($gymA)->create(['name' => 'Bodybuilding']);

        $response = $this->actingAs($gymB, 'sanctum')->postJson('/api/sports', [
            'name' => 'Bodybuilding',
            'day_price' => 5,
            'month_price' => 55,
            'year_price' => 550,
        ]);

        $response->assertCreated();
    }

    public function test_photo_upload_is_stored_and_url_returned(): void
    {
        Storage::fake('public');

        $gym = Gym::factory()->create();
        $photo = UploadedFile::fake()->create('sport.jpg', 10, 'image/jpeg');

        $response = $this->actingAs($gym, 'sanctum')->postJson('/api/sports', [
            'name' => 'Bodybuilding',
            'day_price' => 5,
            'month_price' => 55,
            'year_price' => 550,
            'photo' => $photo,
        ]);

        $response->assertCreated();
        $sport = Sport::first();
        Storage::disk('public')->assertExists($sport->photo);
        $this->assertNotNull($response->json('data.photo'));
    }

    public function test_a_gym_can_list_only_its_own_sports_paginated(): void
    {
        $gym = Gym::factory()->create();
        $otherGym = Gym::factory()->create();
        Sport::factory()->count(3)->for($gym)->create();
        Sport::factory()->count(2)->for($otherGym)->create();

        $response = $this->actingAs($gym, 'sanctum')->getJson('/api/sports');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'data.sports')
            ->assertJsonPath('data.pagination.total', 3);
    }

    public function test_sport_list_can_be_searched_by_name(): void
    {
        $gym = Gym::factory()->create();
        Sport::factory()->for($gym)->create(['name' => 'Bodybuilding']);
        Sport::factory()->for($gym)->create(['name' => 'Yoga']);

        $response = $this->actingAs($gym, 'sanctum')->getJson('/api/sports?search=Body');

        $response->assertOk()->assertJsonCount(1, 'data.sports');
    }

    public function test_sport_list_can_be_sorted_by_price(): void
    {
        $gym = Gym::factory()->create();
        Sport::factory()->for($gym)->create(['name' => 'Cheap', 'day_price' => 5]);
        Sport::factory()->for($gym)->create(['name' => 'Expensive', 'day_price' => 50]);

        $response = $this->actingAs($gym, 'sanctum')
            ->getJson('/api/sports?sort_by=day_price&sort_order=asc');

        $response->assertOk();
        $names = collect($response->json('data.sports'))->pluck('name')->all();
        $this->assertSame(['Cheap', 'Expensive'], $names);
    }

    public function test_a_gym_can_view_its_own_sport_details_with_counts(): void
    {
        $gym = Gym::factory()->create();
        $sport = Sport::factory()->for($gym)->create([
            'name' => 'Bodybuilding',
            'day_price' => 5,
            'month_price' => 55,
            'year_price' => 550,
        ]);

        Coach::factory()->count(4)->for($gym)->create(['sport_id' => $sport->id]);

        $clients = Client::factory()->count(3)->for($gym)->create();
        foreach ($clients as $client) {
            $client->sports()->attach($sport->id);
        }

        $response = $this->actingAs($gym, 'sanctum')->getJson("/api/sports/{$sport->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Bodybuilding')
            ->assertJsonPath('data.number_of_coaches', 4)
            ->assertJsonPath('data.number_of_clients', 3);
    }

    public function test_a_gym_cannot_view_another_gyms_sport(): void
    {
        $gym = Gym::factory()->create();
        $otherGym = Gym::factory()->create();
        $sport = Sport::factory()->for($otherGym)->create();

        $response = $this->actingAs($gym, 'sanctum')->getJson("/api/sports/{$sport->id}");

        $response->assertStatus(404);
    }

    public function test_a_gym_can_update_its_own_sport(): void
    {
        $gym = Gym::factory()->create();
        $sport = Sport::factory()->for($gym)->create();

        $response = $this->actingAs($gym, 'sanctum')->putJson("/api/sports/{$sport->id}", [
            'name' => 'Updated Name',
            'day_price' => 10,
            'month_price' => 90,
            'year_price' => 900,
        ]);

        $response->assertOk()->assertJsonPath('data.name', 'Updated Name');
        $this->assertDatabaseHas('sports', ['id' => $sport->id, 'name' => 'Updated Name']);
    }

    public function test_omitting_a_price_tier_on_update_clears_it(): void
    {
        $gym = Gym::factory()->create();
        $sport = Sport::factory()->for($gym)->create(['week_price' => 30]);

        $response = $this->actingAs($gym, 'sanctum')->putJson("/api/sports/{$sport->id}", [
            'name' => $sport->name,
            'day_price' => 10,
        ]);

        $response->assertOk()->assertJsonPath('data.week_price', null);
        $this->assertDatabaseHas('sports', ['id' => $sport->id, 'week_price' => null]);
    }

    public function test_a_gym_cannot_update_another_gyms_sport(): void
    {
        $gym = Gym::factory()->create();
        $otherGym = Gym::factory()->create();
        $sport = Sport::factory()->for($otherGym)->create();

        $response = $this->actingAs($gym, 'sanctum')->putJson("/api/sports/{$sport->id}", [
            'name' => 'Hacked',
            'day_price' => 10,
            'month_price' => 90,
            'year_price' => 900,
        ]);

        $response->assertStatus(404);
    }

    public function test_a_gym_can_soft_delete_its_own_sport(): void
    {
        $gym = Gym::factory()->create();
        $sport = Sport::factory()->for($gym)->create();

        $response = $this->actingAs($gym, 'sanctum')->deleteJson("/api/sports/{$sport->id}");

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertSoftDeleted('sports', ['id' => $sport->id]);
    }

    public function test_a_gym_cannot_delete_another_gyms_sport(): void
    {
        $gym = Gym::factory()->create();
        $otherGym = Gym::factory()->create();
        $sport = Sport::factory()->for($otherGym)->create();

        $response = $this->actingAs($gym, 'sanctum')->deleteJson("/api/sports/{$sport->id}");

        $response->assertStatus(404);
        $this->assertDatabaseHas('sports', ['id' => $sport->id, 'deleted_at' => null]);
    }

    public function test_a_soft_deleted_sport_cannot_be_assigned_to_a_new_client(): void
    {
        $gym = Gym::factory()->create();
        $sport = Sport::factory()->for($gym)->create();
        $coach = Coach::factory()->for($gym)->create(['sport_id' => $sport->id]);
        $sport->delete();

        $response = $this->actingAs($gym, 'sanctum')->postJson('/api/clients', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'age' => 25,
            'email' => 'john@doe.test',
            'phone' => '123456789',
            'registration_type' => 'month',
            'sports' => [$sport->id],
            'coach_id' => $coach->id,
        ]);

        $response->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_guest_cannot_access_sport_routes(): void
    {
        $this->getJson('/api/sports')->assertStatus(401);
    }
}
