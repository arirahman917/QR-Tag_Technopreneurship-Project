<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CustomerController;
use App\Http\Middleware\AdminAuth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root to admin login
Route::get('/', function () {
    return redirect()->route('admin.login');
});

// ========================
// AUTH ROUTES (Admin Login)
// ========================
Route::prefix('admin')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'login'])->name('admin.login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');
});

// ========================
// ADMIN ROUTES (Protected)
// ========================
Route::prefix('admin')->middleware(AdminAuth::class)->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    // API-style CRUD routes for AJAX
    Route::get('/api/{category}', [AdminController::class, 'getRecords'])->name('admin.api.index');
    Route::post('/api/{category}', [AdminController::class, 'store'])->name('admin.api.store');
    Route::post('/api/{category}/{id}', [AdminController::class, 'update'])->name('admin.api.update');
    Route::delete('/api/{category}/{id}', [AdminController::class, 'destroy'])->name('admin.api.destroy');

    // QR Generation
    Route::get('/api/{category}/{id}/qr', [AdminController::class, 'generateQR'])->name('admin.api.qr');
});

// ========================
// FILE SERVING (GridFS)
// ========================
Route::get('/file/{id}', [AdminController::class, 'serveFile'])->name('file.serve');

// ========================
// CUSTOMER ROUTES (Public)
// ========================
Route::get('/tag/{category}/{qr_token}', [CustomerController::class, 'show'])->name('customer.show');
