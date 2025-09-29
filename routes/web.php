<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Auth::routes();

Route::get('/', [App\Http\Controllers\DashboardController::class, 'index'])->name('home');

Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'login'])->name('login');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'loginProcess'])->name('login.process');
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'roles:2,3'])->prefix('import-data')->name('import-data.')->group(function () {
    Route::get('/', function () {
        return view('import-data.index');
    })->name('index');
});
Route::middleware(['auth', 'roles:2,3'])->prefix('verification-request')->name('verification-request.')->group(function () {
    Route::get('/', function () {
        return view('verification-request.index');
    })->name('index');
});
Route::middleware(['auth', 'roles:2,3'])->prefix('raised-issue')->name('raised-issue.')->group(function () {
    Route::get('/', function () {
        return view('raised-issue.index');
    })->name('index');
});
Route::middleware(['auth', 'roles:2,3'])->prefix('reported-incidents')->name('reported-incidents.')->group(function () {
    Route::get('/', function () {
        return view('reported-incidents.index');
    })->name('index');
});
Route::middleware(['auth', 'roles:2,3'])->prefix('import-data')->name('import-data.')->group(function () {
    Route::get('/', [App\Http\Controllers\ImportData\ImportDataController::class, 'index'])->name('index');
    Route::get('/import', [App\Http\Controllers\ImportData\ImportDataController::class, 'import'])->name('import');
});
