<?php

use App\Http\Controllers\Api\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\CoachController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SportController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::put('change-password', [AuthController::class, 'changePassword']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index']);
        Route::get('charts/registrations', [DashboardController::class, 'registrationsChart']);
        Route::get('charts/income-by-sport', [DashboardController::class, 'incomeBySportChart']);
    });

    Route::get('profile', [ProfileController::class, 'show']);
    Route::put('profile', [ProfileController::class, 'update']);

    Route::apiResource('clients', ClientController::class);

    Route::apiResource('sports', SportController::class);

    Route::get('coaches/by-sport', [CoachController::class, 'bySport']);
    Route::apiResource('coaches', CoachController::class);

    Route::apiResource('expenses', ExpenseController::class);
});

Route::prefix('admin')->group(function () {
    Route::post('login', [AdminAuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::get('users', [AdminUserController::class, 'index']);
        Route::get('users/{id}', [AdminUserController::class, 'show']);
        Route::get('profile', [AdminProfileController::class, 'show']);
        Route::put('profile/password', [AdminProfileController::class, 'changePassword']);
    });
});
