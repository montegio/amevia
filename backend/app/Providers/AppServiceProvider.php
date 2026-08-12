<?php

namespace App\Providers;

use App\Domain\Family\Models\Family;
use App\Domain\Family\Policies\FamilyPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Family::class, FamilyPolicy::class);
    }
}
