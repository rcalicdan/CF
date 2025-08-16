<?php

namespace App\Providers;

use App\Models\ProcessingCost;
use App\Policies\ProcessingCostPolicy;
use Gate;
use Illuminate\Support\ServiceProvider;

class GateServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        ProcessingCost::class => ProcessingCostPolicy::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Gate::define('generate-qr-code', function ($user) {
            return $user->isAdmin() || $user->isEmployee();
        });
    }
}
