<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstallmentController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Auth::routes();

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::resource('sales', SaleController::class)->except('show');
    Route::get('/sales/{sale}/pdf', [SaleController::class, 'pdf'])->name('sales.pdf');
    Route::get('/sales/{sale}/installments/{installment}/edit', [InstallmentController::class, 'edit'])->name('sales.installments.edit');
    Route::put('/sales/{sale}/installments/{installment}', [InstallmentController::class, 'update'])->name('sales.installments.update');

    Route::resource('customers', CustomerController::class)->except('show');
    Route::resource('products', ProductController::class)->except('show');
    Route::resource('payment-methods', PaymentMethodController::class)->except('show');
});
