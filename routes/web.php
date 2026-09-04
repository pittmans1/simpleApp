<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardWidgetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\TenantNotificationController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\OperationsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', fn () => view('login'))->name('login');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
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
    Route::get('/operations/docker', [OperationsController::class, 'docker']);
    Route::get('/operations/stocks', [OperationsController::class, 'stocks']);
});

Route::middleware(['auth', 'tenant'])->prefix('tenants/{tenant}')->group(function (): void {
    Route::get('dashboard', [DashboardController::class, 'tenant'])->name('tenant.dashboard');
    Route::apiResource('dashboard/widgets', DashboardWidgetController::class)->except(['show']);
    Route::get('notifications', [TenantNotificationController::class, 'index']);
    Route::patch('notifications/{notification}/read', [TenantNotificationController::class, 'read']);
    Route::get('audit-logs', [AuditLogController::class, 'index']);
    Route::get('admin/users', [AdminController::class, 'users']);
    Route::patch('admin/users/{user}', [AdminController::class, 'updateUser']);
    Route::patch('admin/tenant', [AdminController::class, 'updateTenant']);
    Route::post('admin/commands', [AdminController::class, 'command']);
});
Route::middleware('admin')->prefix('admin')->group(function (): void {
    Route::get('audit-logs', [AdminController::class, 'auditLogs']);
});