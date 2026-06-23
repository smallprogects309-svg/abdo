<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web API Routes (Browser)
|--------------------------------------------------------------------------
|
| These routes are specifically for Web clients (React, Vue, etc)
| Base URL: /api/v1/web/
|
*/

Route::middleware(['api'])->group(function () {
    // Web-specific public routes
});

Route::middleware(['api', 'auth:sanctum'])->group(function () {
    // Web-specific authenticated routes
});
