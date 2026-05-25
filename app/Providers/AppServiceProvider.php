<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
  Mail::extend('brevo', function (array $config) {
        return (new BrevoTransportFactory)->create(
            new Dsn(
                'brevo+api',
                'default',
                config('services.brevo.key')
            )
        );
    });
        Gate::define('admin', function ($user) {
            return $user->role >= 1;
        });

        Gate::define('super-admin', function ($user) {
            return $user->role >= 2;
        });

            Gate::define('can-send-money', function ($user) {
                return $user->role >= 0; // all users can send money
            });
    }
}
