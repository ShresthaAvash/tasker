<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('is-superadmin', function (User $user) {
            if($user->type == 'S'){
                return true;
            }
            return false;
        });

        Gate::define('is-organization', function (User $user) {
            return $user->type === 'O';
        });

        Gate::define('is-client', function (User $user) {
            return $user->type === 'C';
        });

        Gate::define('is-staff', function (User $user) {
            return $user->type === 'T';
        });
    }
}
