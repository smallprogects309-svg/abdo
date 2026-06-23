<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\{Exceptions, Middleware};

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // API Core Middleware - تطبيق على كل API routes
        $middleware->api(append: [
            \App\Http\Middleware\Core\ApiMiddleware::class,
            \App\Http\Middleware\Core\CorsMiddleware::class,
        ]);
        
        // نظام الـ Aliases - استخدام بسيط في الـ routes
        $middleware->alias([
            // Admin Middleware
            'admin' => \App\Http\Middleware\Admin\CheckAdminRole::class,
            
            // User Middleware
            'auth_user' => \App\Http\Middleware\User\CheckUserAuthenticated::class,
            'student' => \App\Http\Middleware\User\CheckUserRole::class,
            
            // Core Middleware
            'version_check' => \App\Http\Middleware\Core\VersionCheck::class,
        ]);
        
        // Remove CSRF from API routes (Sanctum handles auth)
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
