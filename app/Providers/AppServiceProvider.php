<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Define gates for additional access control
        Gate::define('verify-users', function ($user) {
            return $user->hasRole('Administrator');
        });

        Gate::define('manage-documents', function ($user) {
            return $user->hasAnyRole(['Administrator', 'LGU Staff']);
        });

        Gate::define('view-all-documents', function ($user) {
            return $user->hasRole('Administrator');
        });

        Gate::define('archive-documents', function ($user) {
            return $user->hasAnyRole(['Administrator', 'Department Head']);
        });

        Gate::define('set-priority', function ($user) {
            return $user->hasRole('Administrator');
        });
    }
}

