<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\AccountTypeController;
use App\Http\Controllers\AccountController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/documentation', [DocumentationController::class, 'index'])->name('documentation');

// Account Type Routes
Route::prefix('api/account-types')->group(function () {
    Route::get('/', [AccountTypeController::class, 'index'])->name('account-types.index');
    Route::post('/', [AccountTypeController::class, 'store'])->name('account-types.store');
    Route::get('/{account_type}', [AccountTypeController::class, 'show'])->name('account-types.show');
    Route::put('/{account_type}', [AccountTypeController::class, 'update'])->name('account-types.update');
    Route::patch('/{account_type}', [AccountTypeController::class, 'update'])->name('account-types.update');
    Route::delete('/{account_type}', [AccountTypeController::class, 'destroy'])->name('account-types.destroy');
});

// Account Routes
Route::prefix('api/accounts')->group(function () {
    Route::get('/', [AccountController::class, 'index'])->name('accounts.index');
    Route::post('/', [AccountController::class, 'store'])->name('accounts.store');
    Route::get('/{account}', [AccountController::class, 'show'])->name('accounts.show');
    Route::put('/{account}', [AccountController::class, 'update'])->name('accounts.update');
    Route::patch('/{account}', [AccountController::class, 'update'])->name('accounts.update');
    Route::delete('/{account}', [AccountController::class, 'destroy'])->name('accounts.destroy');
    
    // Helper routes
    Route::get('/options/account-types', [AccountController::class, 'getAccountTypes'])->name('accounts.account-types');
    Route::get('/options/parent-accounts', [AccountController::class, 'getParentAccounts'])->name('accounts.parent-accounts');
});
