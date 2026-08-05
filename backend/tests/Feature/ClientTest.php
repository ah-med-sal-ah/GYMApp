<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Coach;
use App\Models\Gym;
use App\Models\Sport;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    private function coachFor(Gym $gym, Sport $sport): Coach
    {
        return Coach::factory()->for($gym)->create(['sport_id' => $sport->id]);
    }

    public function test_a_gym_can_create_a_client_with_sports(): void
    {
        Carbon::setTestNow('2026-01-01');

        $gym = Gym::factory()->create();
        $sport = Sport::factory()->for($gym)->create();
        $coach = $this->coachFor($gym, $sport);

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

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.first_name', 'John')
            ->assertJsonPath('data.registration_start', '2026-01-01')
            ->assertJsonPath('data.registration_end', '2026-02-01')
            ->assertJsonPath('data.remaining_days', 31)
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.sports_count', 1)
            ->assertJsonPath('data.coach.id', $coach->id);

        $this->assertDatabaseHas('clients', [
            'gym_id' => $gym->id,
            'email' => 'john@doe.test',
            'registration_type' => 'month',
            'coach_id' => $coach->id,
        ]);

        Carbon::setTestNow();
    }

    public function test_registration_end_is_calculated_for_each_type(): void
    {
        Carbon::setTestNow('2026-01-01');

        $gym = Gym::factory()->create();
        $sport = Sport::factory()->for($gym)->create();
        $coach = $this->coachFor($gym, $sport);

        $cases = [
            'day' => '2026-01-02',
            'week' => '2026-01-08',
            'month' => '2026-02-01',
            'year' => '2027-01-01',
        ];

        foreach ($cases as $type => $expectedEnd) {
            $response = $this->actingAs($gym, 'sanctum')->postJson('/api/clients', [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'age' => 25,
                'email' => "john-{$type}@doe.test",
                'phone' => '123456789',
                'registration_type' => $type,
                'sports' => [$sport->id],
                'coach_id' => $coach->id,
            ]);

            $response->assertCreated()->assertJsonPath('data.registration_end', $expectedEnd);
        }

        Carbon::setTestNow();
    }

    public function test_creating_a_client_requires_at_least_one_sport(): void
    {
        $gym = Gym::factory()->create();
        $sport = Sport::factory()->for($gym)->create();
        $coach = $this->coachFor($gym, $sport);

        $response = $this->actingAs($gym, 'sanctum')->postJson('/api/clients', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'age' => 25,
            'email' => 'john@doe.test',
            'phone' => '123456789',
            'registration_type' => 'month',
            'sports' => [],
            'coach_id' => $coach->id,
        ]);

        $response->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_creating_a_client_requires_a_coach(): void
    {
        $gym = Gym::factory()->create();
        $sport = Sport::factory()->for($gym)->create();

        $response = $this->actingAs($gym, 'sanctum')->postJson('/api/clients', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'age' => 25,
            'email' => 'john@doe.test',
            'phone' => '123456789',
            'registration_type' => 'month',
            'sports' => [$sport->id],
        ]);

        $response->assertStatus(422)->assertJsonPath('success', false);
        $response->assertJsonValidationErrors('coach_id');
    }

    public function test_a_gym_cannot_assign_another_gyms_sport(): void
    {
        $gym = Gym::factory()->create();
        $sport = Sport::factory()->for($gym)->create();
        $coach = $this->coachFor($gym, $sport);
        $otherGym = Gym::factory()->create();
        $otherSport = Sport::factory()->for($otherGym)->create();

        $response = $this->actingAs($gym, 'sanctum')->postJson('/api/clients', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'age' => 25,
            'email' => 'john@doe.test',
            'phone' => '123456789',
            'registration_type' => 'month',
            'sports' => [$otherSport->id],
            'coach_id' => $coach->id,
        ]);

        $response->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_a_gym_cannot_assign_another_gyms_coach(): void
    {
        $gym = Gym::factory()->create();
        $sport = Sport::factory()->for($gym)->create();
        $otherGym = Gym::factory()->create();
        $otherSport = Sport::factory()->for($otherGym)->create();
        $otherCoach = $this->coachFor($otherGym, $otherSport);

        $response = $this->actingAs($gym, 'sanctum')->postJson('/api/clients', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'age' => 25,
            'email' => 'john@doe.test',
            'phone' => '123456789',
            'registration_type' => 'month',
            'sports' => [$sport->id],
            'coach_id' => $otherCoach->id,
        ]);

        $response->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_registration_type_must_be_offered_by_the_selected_sport(): void
    {
        $gym = Gym::factory()->create();
        $sport = Sport::factory()->for($gym)->create(['week_price' => null]);
        $coach = $this->coachFor($gym, $sport);

        $response = $this->actingAs($gym, 'sanctum')->postJson('/api/clients', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'age' => 25,
            'email' => 'john@doe.test',
            'phone' => '123456789',
            'registration_type' => 'week',
            'sports' => [$sport->id],
            'coach_id' => $coach->id,
        ]);

        $response->assertStatus(422)->assertJsonPath('success', false);
        $response->assertJsonValidationErrors('registration_type');
    }

    public function test_coach_must_teach_at_least_one_selected_sport(): void
    {
        $gym = Gym::factory()->create();
        $sport = Sport::factory()->for($gym)->create();
        $unrelatedSport = Sport::factory()->for($gym)->create();
        $coach = $this->coachFor($gym, $unrelatedSport);

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
        $response->assertJsonValidationErrors('coach_id');
    }

    public function test_age_must_be_within_range(): void
    {
        $gym = Gym::factory()->create();
        $sport = Sport::factory()->for($gym)->create();
        $coach = $this->coachFor($gym, $sport);

        $response = $this->actingAs($gym, 'sanctum')->postJson('/api/clients', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'age' => 121,
            'email' => 'john@doe.test',
            'phone' => '123456789',
            'registration_type' => 'month',
            'sports' => [$sport->id],
            'coach_id' => $coach->id,
        ]);

        $response->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_email_must_be_unique_per_gym(): void
    {
        $gym = Gym::factory()->create();
        $sport = Sport::factory()->for($gym)->create();
        $coach = $this->coachFor($gym, $sport);
        Client::factory()->for($gym)->create(['email' => 'john@doe.test']);

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

    public function test_same_email_is_allowed_across_different_gyms(): void
    {
        $gymA = Gym::factory()->create();
        $gymB = Gym::factory()->create();
        Client::factory()->for($gymA)->create(['email' => 'shared@doe.test']);
        $sport = Sport::factory()->for($gymB)->create();
        $coach = $this->coachFor($gymB, $sport);

        $response = $this->actingAs($gymB, 'sanctum')->postJson('/api/clients', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'age' => 25,
            'email' => 'shared@doe.test',
            'phone' => '123456789',
            'registration_type' => 'month',
            'sports' => [$sport->id],
            'coach_id' => $coach->id,
        ]);

        $response->assertCreated();
    }

    public function test_photo_upload_is_stored_and_url_returned(): void
    {
        Storage::fake('public');

        $gym = Gym::factory()->create();
        $sport = Sport::factory()->for($gym)->create();
        $coach = $this->coachFor($gym, $sport);
        $photo = UploadedFile::fake()->create('avatar.jpg', 10, 'image/jpeg');

        $response = $this->actingAs($gym, 'sanctum')->postJson('/api/clients', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'age' => 25,
            'email' => 'john@doe.test',
            'phone' => '123456789',
            'registration_type' => 'month',
            'sports' => [$sport->id],
            'coach_id' => $coach->id,
            'photo' => $photo,
        ]);

        $response->assertCreated();
        $client = Client::first();
        Storage::disk('public')->assertExists($client->photo);
        $this->assertNotNull($response->json('data.photo'));
    }

    public function test_a_gym_can_list_only_its_own_clients_paginated(): void
    {
        $gym = Gym::factory()->create();
        $otherGym = Gym::factory()->create();
        Client::factory()->count(3)->for($gym)->create();
        Client::factory()->count(2)->for($otherGym)->create();

        $response = $this->actingAs($gym, 'sanctum')->getJson('/api/clients');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'data.clients')
            ->assertJsonPath('data.pagination.total', 3);
    }

    public function test_client_list_can_be_searched(): void
    {
        $gym = Gym::factory()->create();
        Client::factory()->for($gym)->create(['first_name' => 'Alice']);
        Client::factory()->for($gym)->create(['first_name' => 'Bob']);

        $response = $this->actingAs($gym, 'sanctum')->getJson('/api/clients?search=Alice');

        $response->assertOk()->assertJsonCount(1, 'data.clients');
    }

    public function test_client_list_can_be_sorted(): void
    {
        $gym = Gym::factory()->create();
        Client::factory()->for($gym)->create(['first_name' => 'Zack', 'age' => 20]);
        Client::factory()->for($gym)->create(['first_name' => 'Amy', 'age' => 40]);

        $response = $this->actingAs($gym, 'sanctum')
            ->getJson('/api/clients?sort_by=first_name&sort_order=asc');

        $response->assertOk();
        $names = collect($response->json('data.clients'))->pluck('first_name')->all();
        $this->assertSame(['Amy', 'Zack'], $names);
    }

    public function test_a_gym_can_view_its_own_client_details(): void
    {
        $gym = Gym::factory()->create();
        $client = Client::factory()->for($gym)->create();

        $response = $this->actingAs($gym, 'sanctum')->getJson("/api/clients/{$client->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $client->id);
    }

    public function test_client_details_include_assigned_coach(): void
    {
        $gym = Gym::factory()->create();
        $sport = Sport::factory()->for($gym)->create();
        $coach = $this->coachFor($gym, $sport);
        $client = Client::factory()->for($gym)->for($coach, 'coach')->create();
        $client->sports()->attach($sport->id);

        $response = $this->actingAs($gym, 'sanctum')->getJson("/api/clients/{$client->id}");

        $response->assertOk()
            ->assertJsonPath('data.coach.id', $coach->id)
            ->assertJsonPath('data.coach.name', "{$coach->first_name} {$coach->last_name}");
    }

    public function test_client_details_coach_is_null_when_unassigned(): void
    {
        $gym = Gym::factory()->create();
        $client = Client::factory()->for($gym)->create(['coach_id' => null]);

        $response = $this->actingAs($gym, 'sanctum')->getJson("/api/clients/{$client->id}");

        $response->assertOk()->assertJsonPath('data.coach', null);
    }

    public function test_a_gym_cannot_view_another_gyms_client(): void
    {
        $gym = Gym::factory()->create();
        $otherGym = Gym::factory()->create();
        $client = Client::factory()->for($otherGym)->create();

        $response = $this->actingAs($gym, 'sanctum')->getJson("/api/clients/{$client->id}");

        $response->assertStatus(404);
    }

    public function test_remaining_days_is_zero_and_status_expired_when_past_due(): void
    {
        $gym = Gym::factory()->create();
        $client = Client::factory()->for($gym)->create([
            'registration_start' => Carbon::today()->subMonth(),
            'registration_end' => Carbon::today()->subDay(),
        ]);

        $response = $this->actingAs($gym, 'sanctum')->getJson("/api/clients/{$client->id}");

        $response->assertOk()
            ->assertJsonPath('data.remaining_days', 0)
            ->assertJsonPath('data.status', 'expired');
    }

    public function test_a_gym_can_update_its_own_client(): void
    {
        $gym = Gym::factory()->create();
        $sport = Sport::factory()->for($gym)->create();
        $coach = $this->coachFor($gym, $sport);
        $client = Client::factory()->for($gym)->create();

        $response = $this->actingAs($gym, 'sanctum')->putJson("/api/clients/{$client->id}", [
            'first_name' => 'Updated',
            'last_name' => $client->last_name,
            'age' => $client->age,
            'email' => $client->email,
            'phone' => $client->phone,
            'registration_type' => $client->registration_type,
            'sports' => [$sport->id],
            'coach_id' => $coach->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.first_name', 'Updated')
            ->assertJsonPath('data.coach.id', $coach->id);
        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'first_name' => 'Updated',
            'coach_id' => $coach->id,
        ]);
    }

    public function test_updating_registration_type_recalculates_registration_end(): void
    {
        Carbon::setTestNow('2026-01-01');

        $gym = Gym::factory()->create();
        $sport = Sport::factory()->for($gym)->create();
        $coach = $this->coachFor($gym, $sport);
        $client = Client::factory()->for($gym)->create([
            'registration_type' => 'month',
            'registration_start' => Carbon::parse('2026-01-01'),
            'registration_end' => Carbon::parse('2026-02-01'),
        ]);

        $response = $this->actingAs($gym, 'sanctum')->putJson("/api/clients/{$client->id}", [
            'first_name' => $client->first_name,
            'last_name' => $client->last_name,
            'age' => $client->age,
            'email' => $client->email,
            'phone' => $client->phone,
            'registration_type' => 'year',
            'sports' => [$sport->id],
            'coach_id' => $coach->id,
        ]);

        $response->assertOk()->assertJsonPath('data.registration_end', '2027-01-01');

        Carbon::setTestNow();
    }

    public function test_a_gym_cannot_update_another_gyms_client(): void
    {
        $gym = Gym::factory()->create();
        $sport = Sport::factory()->for($gym)->create();
        $coach = $this->coachFor($gym, $sport);
        $otherGym = Gym::factory()->create();
        $client = Client::factory()->for($otherGym)->create();

        $response = $this->actingAs($gym, 'sanctum')->putJson("/api/clients/{$client->id}", [
            'first_name' => 'Hacked',
            'last_name' => $client->last_name,
            'age' => $client->age,
            'email' => $client->email,
            'phone' => $client->phone,
            'registration_type' => $client->registration_type,
            'sports' => [$sport->id],
            'coach_id' => $coach->id,
        ]);

        $response->assertStatus(404);
    }

    public function test_a_gym_can_soft_delete_its_own_client(): void
    {
        $gym = Gym::factory()->create();
        $client = Client::factory()->for($gym)->create();

        $response = $this->actingAs($gym, 'sanctum')->deleteJson("/api/clients/{$client->id}");

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertSoftDeleted('clients', ['id' => $client->id]);
    }

    public function test_a_gym_cannot_delete_another_gyms_client(): void
    {
        $gym = Gym::factory()->create();
        $otherGym = Gym::factory()->create();
        $client = Client::factory()->for($otherGym)->create();

        $response = $this->actingAs($gym, 'sanctum')->deleteJson("/api/clients/{$client->id}");

        $response->assertStatus(404);
        $this->assertDatabaseHas('clients', ['id' => $client->id, 'deleted_at' => null]);
    }
}
