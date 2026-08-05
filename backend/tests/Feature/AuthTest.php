<?php

namespace Tests\Feature;

use App\Models\Gym;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_gym_can_register(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Iron Gym',
            'email' => 'iron@gym.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.gym.email', 'iron@gym.test')
            ->assertJsonStructure(['data' => ['gym', 'token']]);

        $this->assertDatabaseHas('gyms', ['email' => 'iron@gym.test']);
    }

    public function test_registration_fails_with_duplicate_email(): void
    {
        Gym::factory()->create(['email' => 'iron@gym.test']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Iron Gym',
            'email' => 'iron@gym.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_a_gym_can_login_with_correct_credentials(): void
    {
        Gym::factory()->create([
            'email' => 'iron@gym.test',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'iron@gym.test',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['gym', 'token']]);
    }

    public function test_login_fails_with_incorrect_credentials(): void
    {
        Gym::factory()->create([
            'email' => 'iron@gym.test',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'iron@gym.test',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_authenticated_gym_can_fetch_its_own_profile(): void
    {
        $gym = Gym::factory()->create();

        $response = $this->actingAs($gym, 'sanctum')->getJson('/api/auth/me');

        $response->assertOk()->assertJsonPath('data.id', $gym->id);
    }

    public function test_guest_cannot_access_protected_routes(): void
    {
        $this->getJson('/api/auth/me')->assertStatus(401);
        $this->getJson('/api/dashboard')->assertStatus(401);
        $this->getJson('/api/clients')->assertStatus(401);
    }

    public function test_authenticated_gym_can_logout(): void
    {
        $gym = Gym::factory()->create();
        $token = $gym->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/auth/logout');

        $response->assertOk()->assertJsonPath('success', true);
    }

    public function test_authenticated_gym_can_change_password(): void
    {
        $gym = Gym::factory()->create(['password' => bcrypt('old-password')]);

        $response = $this->actingAs($gym, 'sanctum')->putJson('/api/auth/change-password', [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertOk()->assertJsonPath('success', true);

        $this->assertTrue(password_verify('new-password', $gym->fresh()->password));
    }

    public function test_change_password_fails_with_wrong_current_password(): void
    {
        $gym = Gym::factory()->create(['password' => bcrypt('old-password')]);

        $response = $this->actingAs($gym, 'sanctum')->putJson('/api/auth/change-password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertStatus(422)->assertJsonPath('success', false);
    }
}
