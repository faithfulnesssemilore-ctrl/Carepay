<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Model;
use App\Models\Transaction;
use App\Models\ScheduledPayment;
use App\Models\Wallet;
use App\Livewire\DashboardPage;
use App\Livewire\SendMoney;
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
        Model::preventLazyLoading(!app()->isProduction());
     
        Model::shouldBeStrict(!app()->isProduction());
 
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
        Gate::define('admin', function ($user) {
            return $user->role >= 1;
        });

        Gate::define('super-admin', function ($user) {
            return $user->role >= 2;
        });

        Gate::define('can-send-money', function ($user) {
            return $user->role >= 0; 
        });
    }
}