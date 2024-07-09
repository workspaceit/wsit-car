<?php

namespace App\Providers;

use App\Models\Car;
use App\Models\Issue;
use App\Models\Dealer;
use App\Models\Customer;
use App\Policies\CarPolicy;
use App\Models\SalesContract;
use App\Policies\IssuePolicy;
use App\Policies\DealerPolicy;
use Laravel\Passport\Passport;
use App\Policies\CustomerPolicy;
use App\Policies\SalesContractPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Car::class => CarPolicy::class,
        Dealer::class => DealerPolicy::class,
        Issue::class => IssuePolicy::class,
        Customer::class => CustomerPolicy::class,
        SalesContract::class => SalesContractPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();
    }
}
