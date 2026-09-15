<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\MarketingAnalyticsSnapshotController;
use App\Http\Controllers\McpIntegrationController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
// Marketing & Analytics
Route::get('/analytics', [MarketingAnalyticsSnapshotController::class, 'index'])->name('analytics.index');
Route::get('/analytics/{period}', [MarketingAnalyticsSnapshotController::class, 'show'])->name('analytics.show');
// Orders, Payments & Fulfillment
Route::resource('orders', OrderController::class);
Route::post('/orders/{order}/payments', [PaymentController::class, 'store'])->name('orders.payments.store');
Route::get('/payments/{id}', [PaymentController::class, 'show'])->name('payments.show');
Route::post('/orders/{order}/assign', [AssignmentController::class, 'store'])->name('orders.assign.store');
Route::patch('/assignments/{id}/status', [AssignmentController::class, 'updateStatus'])->name('assignments.update-status');
// Service Catalog & Qualifications
Route::resource('services', ServiceController::class);
// Customer Base
Route::resource('customers', CustomerController::class);
// Team & Users
Route::resource('users', UserController::class);
// Marketing Campaigns
Route::resource('campaigns', CampaignController::class);
// Traffic Channels
Route::resource('channels', ChannelController::class);
});

require __DIR__.'/auth.php';

Route::prefix('api/mcp')->group(function () {
    Route::post('/customers', [McpIntegrationController::class, 'createCustomer']);
    Route::post('/channels', [McpIntegrationController::class, 'createChannel']);
    Route::post('/campaigns', [McpIntegrationController::class, 'createCampaign']);
    Route::post('/orders', [McpIntegrationController::class, 'createOrder']);
    Route::get('/services/{serviceId}/masters', [McpIntegrationController::class, 'findMastersForService']);
    Route::post('/assignments', [McpIntegrationController::class, 'createAssignment']);
    Route::post('/bookings/complete-transaction', [McpIntegrationController::class, 'createCompleteBooking']);
});
