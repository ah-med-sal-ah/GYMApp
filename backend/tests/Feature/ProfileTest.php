<?php

namespace Tests\Feature;

use App\Models\Gym;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_gym_can_view_its_own_profile(): void
    {
        $gym = Gym::factory()->create([
            'name' => 'Power Gym',
            'username' => 'powergym',
            'email' => 'powergym@example.com',
            'phone' => '20123456',
        ]);

        $response = $this->actingAs($gym, 'sanctum')->getJson('/api/profile');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Power Gym')
            ->assertJsonPath('data.username', 'powergym')
            ->assertJsonPath('data.email', 'powergym@example.com')
            ->assertJsonPath('data.phone', '20123456');
    }

    public function test_a_gym_can_update_its_email_and_phone(): void
    {
        $gym = Gym::factory()->create();

        $response = $this->actingAs($gym, 'sanctum')->putJson('/api/profile', [
            'email' => 'new-email@example.com',
            'phone' => '99887766',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email', 'new-email@example.com')
            ->assertJsonPath('data.phone', '99887766');

        $this->assertDatabaseHas('gyms', [
            'id' => $gym->id,
            'email' => 'new-email@example.com',
            'phone' => '99887766',
        ]);
    }

    public function test_profile_update_requires_a_valid_email(): void
    {
        $gym = Gym::factory()->create();

        $response = $this->actingAs($gym, 'sanctum')->putJson('/api/profile', [
            'email' => 'not-an-email',
            'phone' => '123456',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_profile_update_requires_a_unique_email(): void
    {
        Gym::factory()->create(['email' => 'taken@example.com']);
        $gym = Gym::factory()->create();

        $response = $this->actingAs($gym, 'sanctum')->putJson('/api/profile', [
            'email' => 'taken@example.com',
            'phone' => '123456',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_profile_update_requires_phone(): void
    {
        $gym = Gym::factory()->create();

        $response = $this->actingAs($gym, 'sanctum')->putJson('/api/profile', [
            'email' => 'still-mine@example.com',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('phone');
    }

    public function test_a_gym_can_keep_its_own_email_when_updating(): void
    {
        $gym = Gym::factory()->create(['email' => 'mine@example.com']);

        $response = $this->actingAs($gym, 'sanctum')->putJson('/api/profile', [
            'email' => 'mine@example.com',
            'phone' => '111222',
        ]);

        $response->assertOk();
    }

    public function test_profile_requires_authentication(): void
    {
        $this->getJson('/api/profile')->assertStatus(401);
    }
}
