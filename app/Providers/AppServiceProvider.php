<?php

namespace App\Providers;

use App\Jobs\SendSmsJob;
use App\Sms\CustomSmsApi;
use DB;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->bindMethod([SendSmsJob::class, 'handle'], function (SendSmsJob $job, Application $app) {
            return $job->handle($app->make(CustomSmsApi::class));
        });

        if (config('app.debug')) {
            DB::enableQueryLog();
        }

        Model::automaticallyEagerLoadRelationships();
    }
}
