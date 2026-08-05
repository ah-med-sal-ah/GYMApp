<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminAuthService
{
    public function login(array $credentials): array
    {
        $admin = User::where('email', $credentials['email'])->first();

        if (! $admin || ! Hash::check($credentials['password'], $admin->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $admin->createToken('admin_auth_token')->plainTextToken;

        return [$admin, $token];
    }

    public function changePassword(User $admin, string $currentPassword, string $newPassword): void
    {
        if (! Hash::check($currentPassword, $admin->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $admin->update(['password' => Hash::make($newPassword)]);
    }
}
