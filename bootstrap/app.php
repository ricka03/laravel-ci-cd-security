<?php

use App\Http\Middleware\EnsureOrgAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'org.admin' => EnsureOrgAdmin::class,
            // Lab 4 Part E — Sanctum ability checks. Laravel 11+ does not
            // register these for you; without the aliases the route middleware
            // string 'abilities:...' resolves to nothing.
            'abilities' => CheckAbilities::class,      // token must have ALL listed abilities
            'ability' => CheckForAnyAbility::class,    // token must have ANY of them
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
