<?php

namespace ItmoSoft\FeatureFlags;

use Illuminate\Support\ServiceProvider;

class FeatureFlagsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/feature-flags.php', 'feature-flags');

        $this->app->singleton(FeatureFlagManager::class, function ($app): FeatureFlagManager {
            return new FeatureFlagManager();
        });

        $this->app->alias(FeatureFlagManager::class, 'feature-flags');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/feature-flags.php' => config_path('feature-flags.php'),
        ], 'itmosoft-feature-flags-config');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
