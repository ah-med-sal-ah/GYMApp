<?php

namespace Tests\Feature;

use App\Models\Gym;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_with_correct_credentials(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@yourgym.test',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/admin/login', [
            'email' => 'admin@yourgym.test',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.admin.email', $admin->email)
            ->assertJsonStructure(['data' => ['admin' => ['name', 'username', 'email'], 'token']]);
    }

    public function test_admin_login_fails_with_incorrect_credentials(): void
    {
        User::factory()->create([
            'email' => 'admin@yourgym.test',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/admin/login', [
            'email' => 'admin@yourgym.test',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_a_gyms_credentials_cannot_authenticate_via_admin_login(): void
    {
        Gym::factory()->create([
            'email' => 'gym@yourgym.test',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/admin/login', [
            'email' => 'gym@yourgym.test',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_guest_cannot_access_admin_routes(): void
    {
        $this->getJson('/api/admin/users')->assertStatus(401);
        $this->getJson('/api/admin/profile')->assertStatus(401);
    }

    public function test_a_gym_token_cannot_access_admin_routes(): void
    {
        $gym = Gym::factory()->create();

        $this->actingAs($gym, 'sanctum')->getJson('/api/admin/users')->assertStatus(403);
        $this->actingAs($gym, 'sanctum')->getJson('/api/admin/profile')->assertStatus(403);
    }

    public function test_admin_can_list_users_paginated(): void
    {
        $admin = User::factory()->create();
        Gym::factory()->count(3)->create();

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/admin/users');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'data.users')
            ->assertJsonPath('data.pagination.total', 3);

        $row = $response->json('data.users.0');
        $this->assertArrayHasKey('id', $row);
        $this->assertArrayHasKey('gym_name', $row);
        $this->assertArrayHasKey('username', $row);
        $this->assertArrayNotHasKey('email', $row);
    }

    public function test_users_list_can_be_searched_by_gym_name_username_or_email(): void
    {
        $admin = User::factory()->create();
        Gym::factory()->create(['name' => 'Power Gym', 'username' => 'powergym', 'email' => 'power@gmail.com']);
        Gym::factory()->create(['name' => 'Iron Gym', 'username' => 'irongym', 'email' => 'iron@gmail.com']);

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/admin/users?search=Power');
        $response->assertOk()->assertJsonCount(1, 'data.users');

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/admin/users?search=irongym');
        $response->assertOk()->assertJsonCount(1, 'data.users');

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/admin/users?search=iron@gmail.com');
        $response->assertOk()->assertJsonCount(1, 'data.users');
    }

    public function test_users_list_can_be_sorted_by_gym_name(): void
    {
        $admin = User::factory()->create();
        Gym::factory()->create(['name' => 'Zeta Gym']);
        Gym::factory()->create(['name' => 'Alpha Gym']);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/admin/users?sort_by=gym_name&sort_order=asc');

        $response->assertOk();
        $names = collect($response->json('data.users'))->pluck('gym_name')->all();
        $this->assertSame(['Alpha Gym', 'Zeta Gym'], $names);
    }

    public function test_admin_can_view_user_details(): void
    {
        $admin = User::factory()->create();
        $gym = Gym::factory()->create([
            'name' => 'Power Gym',
            'username' => 'powergym',
            'email' => 'power@gmail.com',
            'phone' => '92147738',
        ]);

        $response = $this->actingAs($admin, 'sanctum')->getJson("/api/admin/users/{$gym->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $gym->id)
            ->assertJsonPath('data.gym_name', 'Power Gym')
            ->assertJsonPath('data.username', 'powergym')
            ->assertJsonPath('data.email', 'power@gmail.com')
            ->assertJsonPath('data.phone', '92147738');

        $data = $response->json('data');
        $this->assertArrayNotHasKey('clients', $data);
        $this->assertArrayNotHasKey('coaches', $data);
        $this->assertArrayNotHasKey('sports', $data);
        $this->assertArrayNotHasKey('expenses', $data);
        $this->assertArrayNotHasKey('total_clients', $data);
        $this->assertArrayNotHasKey('password', $data);
    }

    public function test_viewing_a_nonexistent_user_returns_404(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/admin/users/999999');

        $response->assertStatus(404);
    }

    public function test_admin_can_view_own_profile(): void
    {
        $admin = User::factory()->create([
            'name' => 'Root Admin',
            'username' => 'rootadmin',
            'email' => 'root@yourgym.test',
        ]);

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/admin/profile');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Root Admin')
            ->assertJsonPath('data.username', 'rootadmin')
            ->assertJsonPath('data.email', 'root@yourgym.test');

        $data = $response->json('data');
        $this->assertArrayNotHasKey('password', $data);
        $this->assertArrayNotHasKey('id', $data);
    }

    public function test_admin_can_change_password(): void
    {
        $admin = User::factory()->create(['password' => bcrypt('old-password')]);

        $response = $this->actingAs($admin, 'sanctum')->putJson('/api/admin/profile/password', [
            'current_password' => 'old-password',
            'new_password' => 'new-password',
            'new_password_confirmation' => 'new-password',
        ]);

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertTrue(password_verify('new-password', $admin->fresh()->password));
    }

    public function test_changing_password_fails_with_wrong_current_password(): void
    {
        $admin = User::factory()->create(['password' => bcrypt('old-password')]);

        $response = $this->actingAs($admin, 'sanctum')->putJson('/api/admin/profile/password', [
            'current_password' => 'wrong-password',
            'new_password' => 'new-password',
            'new_password_confirmation' => 'new-password',
        ]);

        $response->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_a_gym_cannot_change_admin_password(): void
    {
        $gym = Gym::factory()->create();

        $response = $this->actingAs($gym, 'sanctum')->putJson('/api/admin/profile/password', [
            'current_password' => 'whatever',
            'new_password' => 'new-password',
            'new_password_confirmation' => 'new-password',
        ]);

        $response->assertStatus(403);
    }
}
