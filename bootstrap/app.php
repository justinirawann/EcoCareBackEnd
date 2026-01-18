<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\CorsMiddleware;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        
        // Exclude CSRF from API routes
        $middleware->validateCsrfTokens(except: [
            'api/*'
        ]);
        
        $middleware->alias([
            'role'       => RoleMiddleware::class,
            'permission' => CheckPermission::class,
            'cors'       => CorsMiddleware::class,
        ]);

        // Add CORS middleware for API routes
        $middleware->api(prepend: [
            CorsMiddleware::class,
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
        
        $middleware->append(EnsureFrontendRequestsAreStateful::class);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
