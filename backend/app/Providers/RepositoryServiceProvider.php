<?php

namespace App\Providers;

use App\Repositories\ClientRepository;
use App\Repositories\CoachRepository;
use App\Repositories\Contracts\ClientRepositoryInterface;
use App\Repositories\Contracts\CoachRepositoryInterface;
use App\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Repositories\Contracts\GymRepositoryInterface;
use App\Repositories\Contracts\SportRepositoryInterface;
use App\Repositories\ExpenseRepository;
use App\Repositories\GymRepository;
use App\Repositories\SportRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(ClientRepositoryInterface::class, ClientRepository::class);
        $this->app->bind(CoachRepositoryInterface::class, CoachRepository::class);
        $this->app->bind(SportRepositoryInterface::class, SportRepository::class);
        $this->app->bind(ExpenseRepositoryInterface::class, ExpenseRepository::class);
        $this->app->bind(GymRepositoryInterface::class, GymRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
