<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // force https in production so css and js load correctly
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        Gate::define('admin', function ($user) {
            return $user->role >= 1;
        });

        Gate::define('super-admin', function ($user) {
            return $user->role >= 2;
        });
    }
}
