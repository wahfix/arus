<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmploymentController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('customers', CustomerController::class);
    Route::resource('customers.employments', EmploymentController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
});

require __DIR__.'/settings.php';
