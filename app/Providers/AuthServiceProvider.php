<?php

namespace App\Providers;

use App\Models\Document;
use App\Models\ProductReception;
use App\Models\Purchase;
use App\Policies\DocumentPolicy;
use App\Policies\ProductReceptionPolicy;
use App\Policies\PurchasePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }
    
    /**
     * The model–policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Document::class => DocumentPolicy::class,
        ProductReception::class => ProductReceptionPolicy::class,
        Purchase::class => PurchasePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
