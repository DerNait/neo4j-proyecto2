<?php

namespace App\Providers;

use App\Services\Neo4jClient;
use Illuminate\Support\ServiceProvider;

class Neo4jServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Neo4jClient::class, function () {
            return new Neo4jClient();
        });
    }

    public function boot(): void
    {
        //
    }
}
