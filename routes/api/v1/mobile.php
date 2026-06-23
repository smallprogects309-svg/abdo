<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile API Routes (React Native)
|--------------------------------------------------------------------------
|
| These routes are specifically for Mobile clients (React Native, Flutter, etc)
| Base URL: /api/v1/mobile/
|
*/

Route::middleware(['api'])->group(function () {
    // Mobile-specific public routes
});

Route::middleware(['api', 'auth:sanctum'])->group(function () {
    // Mobile-specific authenticated routes
});
