<?php

namespace ItmoSoft\ContractPricing;

use Illuminate\Support\ServiceProvider;

class ContractPricingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/contract-pricing.php', 'contract-pricing');
        $this->app->singleton(ContractPriceResolver::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/contract-pricing.php' => config_path('contract-pricing.php'),
        ], 'contract-pricing-config');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
