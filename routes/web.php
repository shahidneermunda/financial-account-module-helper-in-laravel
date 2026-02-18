<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentationController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/documentation', [DocumentationController::class, 'index'])->name('documentation');
