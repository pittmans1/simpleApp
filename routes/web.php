<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardWidgetController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\TenantNotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');
Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect']);
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback']);
Route::middleware('auth')->group(function (): void {
    Route::get('/user/theme', [AuthController::class, 'theme']);
    Route::post('/user/theme', [AuthController::class, 'updateTheme']);
    Route::get('/user/achievements', [AuthController::class, 'achievements']);
    Route::post('/user/achievements', [AuthController::class, 'addAchievement']);
});

Route::middleware(['auth', 'tenant'])->prefix('tenants/{tenant}')->group(function (): void {
    Route::apiResource('dashboard/widgets', DashboardWidgetController::class)->except(['show']);
    Route::get('notifications', [TenantNotificationController::class, 'index']);
    Route::patch('notifications/{notification}/read', [TenantNotificationController::class, 'read']);
    Route::get('audit-logs', [AuditLogController::class, 'index']);
});
