<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('client', function ($user) {
            return $user->default_role === 'CLI';
        });
        Gate::define('admin', function ($user) {
            return $user->default_role === 'DBA';
        });
        Gate::define('readonly', function ($user) {
            return $user->default_role === 'DBRO' || !$user->default_role;
        });
        Gate::define('readwrite', function ($user) {
            return $user->default_role === 'DBRW';
        });
    }
}
