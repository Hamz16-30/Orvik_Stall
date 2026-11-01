<?php

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
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'business' => \App\Http\Middleware\EnsureUserHasBusiness::class,
            'active.account' => \App\Http\Middleware\EnsureAccountIsActive::class,
            'permission' => \App\Http\Middleware\AuthorizePermission::class,
            'platform.admin' => \App\Http\Middleware\EnsurePlatformAdmin::class,
            'feature' => \App\Http\Middleware\EnsureFeature::class,
            'plan.limit' => \App\Http\Middleware\EnsurePlanLimit::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
