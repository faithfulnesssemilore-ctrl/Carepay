<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;

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
        Model::preventLazyLoading(! app()->isProduction());

        Model::shouldBeStrict(! app()->isProduction());

        // Force HTTPS in production
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Register custom Brevo Mail driver
        Mail::extend('brevo', function (array $config) {
            return (new BrevoTransportFactory)->create(
                new Dsn(
                    'brevo+api',
                    'default',
                    config('services.brevo.key')
                )
            );
        });

        // Application Gates
        // Role levels: 0=user, 1=admin, 2=super_admin
        Gate::define('admin', function ($user) {
            return $user->role >= 1; // admin or super_admin
        });

        Gate::define('super-admin', function ($user) {
            return $user->role >= 2; // super_admin only
        });

        Gate::define('can-send-money', function ($user) {
            return $user->role >= 0; // all authenticated users (user, admin, super_admin)
        });
    }
}
