<?php

use App\Models\DailyExpense;
use App\Observers\DailyExpenseObserver;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'buyer' => \App\Http\Middleware\EnsureUserIsBuyer::class,
            'not.buyer' => \App\Http\Middleware\EnsureUserIsNotBuyer::class,
            'admin_or_finance' => \App\Http\Middleware\EnsureUserIsAdminOrFinance::class,
            'auth.internal_api' => \App\Http\Middleware\AuthenticateInternalApiKey::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
