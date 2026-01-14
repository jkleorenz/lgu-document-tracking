<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;

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
            return $user->hasAnyRole(['Administrator', 'Mayor', 'LGU Staff', 'Department Head']);
        });

        Gate::define('view-all-documents', function ($user) {
            return $user->hasAnyRole(['Administrator', 'Mayor']);
        });

        Gate::define('archive-documents', function ($user) {
            // LGU Staff and Department Head have identical archive privileges
            return $user->hasAnyRole(['Administrator', 'Mayor', 'LGU Staff', 'Department Head']);
        });

        Gate::define('set-priority', function ($user) {
            return $user->hasAnyRole(['Administrator', 'Mayor']);
        });

        Gate::define('reset user passwords', function ($user) {
            return $user->hasRole('Administrator');
        });

        Gate::define('view user passwords', function ($user) {
            return $user->hasRole('Administrator');
        });

        // Force HTTPS in production
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}

