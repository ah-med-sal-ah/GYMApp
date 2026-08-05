<?php

namespace App\Services;

use App\Models\Gym;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(array $data): array
    {
        $gym = Gym::create([
            'name' => $data['name'],
            'username' => $this->generateUsername($data['name']),
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = $gym->createToken('auth_token')->plainTextToken;

        return [$gym, $token];
    }

    public function login(array $credentials): array
    {
        $gym = Gym::where('email', $credentials['email'])->first();

        if (! $gym || ! Hash::check($credentials['password'], $gym->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $gym->createToken('auth_token')->plainTextToken;

        return [$gym, $token];
    }

    public function logout(Gym $gym): void
    {
        $gym->currentAccessToken()->delete();
    }

    public function changePassword(Gym $gym, string $currentPassword, string $newPassword): void
    {
        if (! Hash::check($currentPassword, $gym->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $gym->update(['password' => Hash::make($newPassword)]);
    }

    private function generateUsername(string $name): string
    {
        $base = Str::slug($name, '') ?: 'gym';
        $username = $base;
        $suffix = 1;

        while (Gym::where('username', $username)->exists()) {
            $suffix++;
            $username = $base.$suffix;
        }

        return $username;
    }
}
