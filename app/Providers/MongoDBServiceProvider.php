<?php

namespace App\Providers;

use App\Services\MongoDBService;
use Illuminate\Support\ServiceProvider;

class MongoDBServiceProvider extends ServiceProvider
{
    /**
     * Register the MongoDB service as a singleton.
     */
    public function register(): void
    {
        $this->app->singleton(MongoDBService::class, function () {
            return new MongoDBService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
