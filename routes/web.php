<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmploymentController;
use App\Http\Controllers\InstallmentController;
use App\Http\Controllers\LoanController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('customers', CustomerController::class);
    Route::resource('customers.employments', EmploymentController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);

    Route::resource('loans', LoanController::class)->except(['destroy']);
    Route::post('loans/preview', [LoanController::class, 'preview'])->name('loans.preview');
    Route::post('loans/{loan}/submit', [LoanController::class, 'submit'])->name('loans.submit');
    Route::post('loans/{loan}/review', [LoanController::class, 'review'])->name('loans.review');
    Route::post('loans/{loan}/approve', [LoanController::class, 'approve'])->name('loans.approve');
    Route::post('loans/{loan}/prepare', [LoanController::class, 'prepare'])->name('loans.prepare');
    Route::post('loans/{loan}/disburse', [LoanController::class, 'disburse'])->name('loans.disburse');
    Route::post('loans/{loan}/reject', [LoanController::class, 'reject'])->name('loans.reject');
    Route::post('loans/{loan}/cancel', [LoanController::class, 'cancel'])->name('loans.cancel');
    Route::post('loans/{loan}/mark-overdue', [LoanController::class, 'markOverdue'])->name('loans.mark-overdue');
    Route::post('loans/{loan}/clear-overdue', [LoanController::class, 'clearOverdue'])->name('loans.clear-overdue');
    Route::post('loans/{loan}/complete', [LoanController::class, 'complete'])->name('loans.complete');
    Route::post('loans/{loan}/default', [LoanController::class, 'default'])->name('loans.default');

    Route::get('installments', [InstallmentController::class, 'index'])->name('installments.index');
});

require __DIR__.'/settings.php';
