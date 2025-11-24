<?php

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
    ->withMiddleware(function (Middleware $middleware) {
        // Middleware aliases (replacing old $middlewareAliases)
        $middleware->alias([
            'check.subscription' => \App\Http\Middleware\CheckSubscription::class,
            'verify.vendor' => \App\Http\Middleware\VerifyVendor::class,
            'verify.store.owner' => \App\Http\Middleware\VerifyStoreOwner::class,
            'validate.session' => \App\Http\Middleware\ValidateSession::class,
        ]);

        

        // API middleware group
        $middleware->api(prepend: [
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();