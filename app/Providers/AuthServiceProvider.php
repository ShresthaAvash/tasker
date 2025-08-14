<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\User; // <-- IMPORTANT: Make sure the User model is imported

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // --- THIS IS THE DEFINITIVE FIX ---

        // Gate to check if a user is a Super Admin
        Gate::define('is-superadmin', function (User $user) {
            return $user->type === 'S';
        });

        // Gate to check if a user is an Organization Owner
        Gate::define('is-organization', function (User $user) {
            return $user->type === 'O';
        });

        // Gate to check if a user is a Staff Member
        Gate::define('is-staff', function (User $user) {
            // Based on your database, staff have types like 'A' and 'T'.
            // Add any other staff types to this array if needed.
            return in_array($user->type, ['A', 'T']);
        });

        // --- END OF FIX ---
    }
}