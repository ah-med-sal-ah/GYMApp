<?php

namespace Tests\Feature;

use App\Models\Coach;
use App\Models\Gym;
use App\Models\Sport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CoachTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_gym_can_create_a_coach_with_a_sport(): void
    {
        $gym = Gym::factory()->create();
        $sport = Sport::factory()->for($gym)->create();

        $response = $this->actingAs($gym, 'sanctum')->postJson('/api/coaches', [
            'first_name' => 'Jamal',
            'last_name' => 'Salah',
            'age' => 35,
            'email' => 'jamal@coach.test',
            'phone' => '123456789',
            'salary' => 2500.50,
            'sport_id' => $sport->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.first_name', 'Jamal')
            ->assertJsonPath('data.salary', 2500.5)
            ->assertJsonPath('data.sport.id', $sport->id)
            ->assertJsonPath('data.sport.name', $sport->name);

        $this->assertDatabaseHas('coaches', [
            'gym_id' => $gym->id,
            'email' => 'jamal@coach.test',
            'sport_id' => $sport->id,
        ]);
    }

    public function test_creating_a_coach_requires_a_sport(): void
    {
        $gym = Gym::factory()->create();

        $response = $this->actingAs($gym, 'sanctum')->postJson('/api/coaches', [
            'first_name' => 'Jamal',
            'last_name' => 'Salah',
            'age' => 35,
            'email' => 'jamal@coach.test',
            'phone' => '123456789',
            'salary' => 2500,
        ]);

        $response->assertStatus(422)->assertJsonPath('success', false);
        $response->assertJsonValidationErrors('sport_id');
    }

    public function test_age_must_be_within_range(): void
    {
        $gym = Gym::factory()->create();
        $sport = Sport::factory()->for($gym)->create();

        $response = $this->actingAs($gym, 'sanctum')->postJson('/api/coaches', [
            'first_name' => 'Jamal',
            'last_name' => 'Salah',
            'age' => 17,
            'email' => 'jamal@coach.test',
            'phone' => '123456789',
            'salary' => 2500,
            'sport_id' => $sport->id,
        ]);

        $response->assertStatus(422)->assertJsonPath('success', false);

        $response = $this->actingAs($gym, 'sanctum')->postJson('/api/coaches', [
            'first_name' => 'Jamal',
            'last_name' => 'Salah',
            'age' => 81,
            'email' => 'jamal2@coach.test',
            'phone' => '123456789',
            'salary' => 2500,
            'sport_id' => $sport->id,
        ]);

        $response->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_salary_must_be_zero_or_greater(): void
    {
        $gym = Gym::factory()->create();
        $sport = Sport::factory()->for($gym)->create();

        $response = $this->actingAs($gym, 'sanctum')->postJson('/api/coaches', [
            'first_name' => 'Jamal',
            'last_name' => 'Salah',
            'age' => 35,
            'email' => 'jamal@coach.test',
            'phone' => '123456789',
            'salary' => -1,
            'sport_id' => $sport->id,
        ]);

        $response->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_email_must_be_unique_per_gym(): void
    {
        $gym = Gym::factory()->create();
        $sport = Sport::factory()->for($gym)->create();
        Coach::factory()->for($gym)->create(['email' => 'jamal@coach.test']);

        $response = $this->actingAs($gym, 'sanctum')->postJson('/api/coaches', [
            'first_name' => 'Jamal',
            'last_name' => 'Salah',
            'age' => 35,
            'email' => 'jamal@coach.test',
            'phone' => '123456789',
            'salary' => 2500,
            'sport_id' => $sport->id,
        ]);

        $response->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_same_email_is_allowed_across_different_gyms(): void
    {
        $gymA = Gym::factory()->create();
        $gymB = Gym::factory()->create();
        Coach::factory()->for($gymA)->create(['email' => 'shared@coach.test']);
        $sport = Sport::factory()->for($gymB)->create();

        $response = $this->actingAs($gymB, 'sanctum')->postJson('/api/coaches', [
            'first_name' => 'Jamal',
            'last_name' => 'Salah',
            'age' => 35,
            'email' => 'shared@coach.test',
            'phone' => '123456789',
            'salary' => 2500,
            'sport_id' => $sport->id,
        ]);

        $response->assertCreated();
    }

    public function test_a_gym_cannot_assign_another_gyms_sport(): void
    {
        $gym = Gym::factory()->create();
        $otherGym = Gym::factory()->create();
        $otherSport = Sport::factory()->for($otherGym)->create();

        $response = $this->actingAs($gym, 'sanctum')->postJson('/api/coaches', [
            'first_name' => 'Jamal',
            'last_name' => 'Salah',
            'age' => 35,
            'email' => 'jamal@coach.test',
            'phone' => '123456789',
            'salary' => 2500,
            'sport_id' => $otherSport->id,
        ]);

        $response->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_photo_upload_is_stored_and_url_returned(): void
    {
        Storage::fake('public');

        $gym = Gym::factory()->create();
        $sport = Sport::factory()->for($gym)->create();
        $photo = UploadedFile::fake()->create('avatar.jpg', 10, 'image/jpeg');

        $response = $this->actingAs($gym, 'sanctum')->postJson('/api/coaches', [
            'first_name' => 'Jamal',
            'last_name' => 'Salah',
            'age' => 35,
            'email' => 'jamal@coach.test',
            'phone' => '123456789',
            'salary' => 2500,
            'sport_id' => $sport->id,
            'photo' => $photo,
        ]);

        $response->assertCreated();
        $coach = Coach::first();
        Storage::disk('public')->assertExists($coach->photo);
        $this->assertNotNull($response->json('data.photo'));
    }

    public function test_a_gym_can_list_only_its_own_coaches_paginated(): void
    {
        $gym = Gym::factory()->create();
        $otherGym = Gym::factory()->create();
        Coach::factory()->count(3)->for($gym)->create();
        Coach::factory()->count(2)->for($otherGym)->create();

        $response = $this->actingAs($gym, 'sanctum')->getJson('/api/coaches');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'data.coaches')
            ->assertJsonPath('data.pagination.total', 3);
    }

    public function test_coach_list_can_be_searched(): void
    {
        $gym = Gym::factory()->create();
        Coach::factory()->for($gym)->create(['first_name' => 'Alice']);
        Coach::factory()->for($gym)->create(['first_name' => 'Bob']);

        $response = $this->actingAs($gym, 'sanctum')->getJson('/api/coaches?search=Alice');

        $response->assertOk()->assertJsonCount(1, 'data.coaches');
    }

    public function test_coach_list_can_be_sorted_by_salary(): void
    {
        $gym = Gym::factory()->create();
        Coach::factory()->for($gym)->create(['first_name' => 'Low', 'salary' => 1000]);
        Coach::factory()->for($gym)->create(['first_name' => 'High', 'salary' => 5000]);

        $response = $this->actingAs($gym, 'sanctum')
            ->getJson('/api/coaches?sort_by=salary&sort_order=asc');

        $response->assertOk();
        $names = collect($response->json('data.coaches'))->pluck('first_name')->all();
        $this->assertSame(['Low', 'High'], $names);
    }

    public function test_coach_list_can_be_filtered_by_sport(): void
    {
        $gym = Gym::factory()->create();
        $sportA = Sport::factory()->for($gym)->create();
        $sportB = Sport::factory()->for($gym)->create();

        Coach::factory()->for($gym)->create(['sport_id' => $sportA->id]);
        Coach::factory()->for($gym)->create(['sport_id' => $sportB->id]);

        $response = $this->actingAs($gym, 'sanctum')->getJson("/api/coaches?sport_id={$sportA->id}");

        $response->assertOk()->assertJsonCount(1, 'data.coaches');
    }

    public function test_a_gym_can_view_its_own_coach_details(): void
    {
        $gym = Gym::factory()->create();
        $sport = Sport::factory()->for($gym)->create();
        $coach = Coach::factory()->for($gym)->create(['sport_id' => $sport->id]);

        $response = $this->actingAs($gym, 'sanctum')->getJson("/api/coaches/{$coach->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $coach->id)
            ->assertJsonPath('data.sport.id', $sport->id);
    }

    public function test_a_gym_cannot_view_another_gyms_coach(): void
    {
        $gym = Gym::factory()->create();
        $otherGym = Gym::factory()->create();
        $coach = Coach::factory()->for($otherGym)->create();

        $response = $this->actingAs($gym, 'sanctum')->getJson("/api/coaches/{$coach->id}");

        $response->assertStatus(404);
    }

    public function test_a_gym_can_update_its_own_coach(): void
    {
        $gym = Gym::factory()->create();
        $sport = Sport::factory()->for($gym)->create();
        $coach = Coach::factory()->for($gym)->create();

        $response = $this->actingAs($gym, 'sanctum')->putJson("/api/coaches/{$coach->id}", [
            'first_name' => 'Updated',
            'last_name' => $coach->last_name,
            'age' => $coach->age,
            'email' => $coach->email,
            'phone' => $coach->phone,
            'salary' => $coach->salary,
            'sport_id' => $sport->id,
        ]);

        $response->assertOk()->assertJsonPath('data.first_name', 'Updated');
        $this->assertDatabaseHas('coaches', ['id' => $coach->id, 'first_name' => 'Updated']);
    }

    public function test_a_gym_cannot_update_another_gyms_coach(): void
    {
        $gym = Gym::factory()->create();
        $sport = Sport::factory()->for($gym)->create();
        $otherGym = Gym::factory()->create();
        $coach = Coach::factory()->for($otherGym)->create();

        $response = $this->actingAs($gym, 'sanctum')->putJson("/api/coaches/{$coach->id}", [
            'first_name' => 'Hacked',
            'last_name' => $coach->last_name,
            'age' => $coach->age,
            'email' => $coach->email,
            'phone' => $coach->phone,
            'salary' => $coach->salary,
            'sport_id' => $sport->id,
        ]);

        $response->assertStatus(404);
    }

    public function test_a_gym_can_soft_delete_its_own_coach(): void
    {
        $gym = Gym::factory()->create();
        $coach = Coach::factory()->for($gym)->create();

        $response = $this->actingAs($gym, 'sanctum')->deleteJson("/api/coaches/{$coach->id}");

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertSoftDeleted('coaches', ['id' => $coach->id]);
    }

    public function test_a_gym_cannot_delete_another_gyms_coach(): void
    {
        $gym = Gym::factory()->create();
        $otherGym = Gym::factory()->create();
        $coach = Coach::factory()->for($otherGym)->create();

        $response = $this->actingAs($gym, 'sanctum')->deleteJson("/api/coaches/{$coach->id}");

        $response->assertStatus(404);
        $this->assertDatabaseHas('coaches', ['id' => $coach->id, 'deleted_at' => null]);
    }

    public function test_by_sport_endpoint_returns_only_coaches_of_that_sport(): void
    {
        $gym = Gym::factory()->create();
        $sportA = Sport::factory()->for($gym)->create();
        $sportB = Sport::factory()->for($gym)->create();

        $coachA = Coach::factory()->for($gym)->create(['first_name' => 'Teaches A', 'sport_id' => $sportA->id]);
        Coach::factory()->for($gym)->create(['first_name' => 'Teaches B', 'sport_id' => $sportB->id]);

        $response = $this->actingAs($gym, 'sanctum')->getJson("/api/coaches/by-sport?sport_id={$sportA->id}");

        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertSame($coachA->id, $response->json('data.0.id'));
        $this->assertArrayHasKey('first_name', $response->json('data.0'));
        $this->assertArrayHasKey('last_name', $response->json('data.0'));
        $this->assertArrayHasKey('photo', $response->json('data.0'));
        $this->assertArrayNotHasKey('salary', $response->json('data.0'));
    }

    public function test_by_sport_endpoint_never_returns_another_gyms_coaches(): void
    {
        $gym = Gym::factory()->create();
        $otherGym = Gym::factory()->create();
        $sport = Sport::factory()->for($gym)->create();
        $otherSport = Sport::factory()->for($otherGym)->create();

        Coach::factory()->for($otherGym)->create(['sport_id' => $otherSport->id]);

        $response = $this->actingAs($gym, 'sanctum')->getJson("/api/coaches/by-sport?sport_id={$sport->id}");

        $response->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_by_sport_endpoint_rejects_a_sport_from_another_gym(): void
    {
        $gym = Gym::factory()->create();
        $otherGym = Gym::factory()->create();
        $otherSport = Sport::factory()->for($otherGym)->create();

        $response = $this->actingAs($gym, 'sanctum')->getJson("/api/coaches/by-sport?sport_id={$otherSport->id}");

        $response->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_guest_cannot_access_coach_routes(): void
    {
        $this->getJson('/api/coaches')->assertStatus(401);
        $this->getJson('/api/coaches/by-sport?sport_id=1')->assertStatus(401);
    }
}
