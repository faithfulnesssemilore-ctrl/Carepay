<?php

use App\Console\Commands\CleanupOldStatements;
use App\Console\Commands\FundUserWalletCommand;
use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\IdempotencyMiddleware;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\RequireTransactionPin;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'ensure.admin' => EnsureAdmin::class,
            'guest' => RedirectIfAuthenticated::class,
            'active' => EnsureAccountIsActive::class,
            'pin' => RequireTransactionPin::class,
            'idempotency' => IdempotencyMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //

    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('statements:cleanup')->dailyAt('02:00');
    })
    ->withCommands([
        FundUserWalletCommand::class,
        CleanupOldStatements::class,
    ])
    ->create();
