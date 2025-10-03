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

Route::get('/', function () {
    if (auth()->check() && auth()->user()->role_id == 1) {
        return app(App\Http\Controllers\DashboardPuskesmasController::class)->index();
    }
    return app(App\Http\Controllers\DashboardController::class)->index();
})->name('home');

Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'login'])->name('login');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'loginProcess'])->name('login.process');
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'roles:2,3'])->prefix('verification-request')->name('verification-request.')->group(function () {
    Route::get('/api/fetch/{status?}', [App\Http\Controllers\VerificationRequest\VerificationRequestController::class, 'fetch'])->name('fetch');
    Route::get('/{status?}', [App\Http\Controllers\VerificationRequest\VerificationRequestController::class, 'index'])->name('index');
    Route::get('/detail/{id}', [App\Http\Controllers\VerificationRequest\VerificationRequestController::class, 'detail'])->name('detail');
});

Route::middleware(['auth', 'roles:2,3'])->prefix('api-verification-request')->name('api-verification-request.')->group(function () {
    Route::POST('/basic-information/{id}', [App\Http\Controllers\VerificationRequest\API\APIVerificationRequestController::class, 'editBasicInformation'])->name('basic-information');
    Route::POST('/delivery-information/{id}', [App\Http\Controllers\VerificationRequest\API\APIVerificationRequestController::class, 'editDeliveryInformation'])->name('delivery-information');
    Route::POST('/uji-fungsi-information/{id}', [App\Http\Controllers\VerificationRequest\API\APIVerificationRequestController::class, 'editUjiFungsiInformation'])->name('uji-fungsi-information');
    Route::POST('/document-information/{id}', [App\Http\Controllers\VerificationRequest\API\APIVerificationRequestController::class, 'editDocumentInformation'])->name('document-information');
    Route::POST('/delivery-verification/{id}', [App\Http\Controllers\VerificationRequest\API\APIVerificationRequestController::class, 'updateDeliveryVerification'])->name('delivery-verification');
    Route::POST('/ujifungsi-verification/{id}', [App\Http\Controllers\VerificationRequest\API\APIVerificationRequestController::class, 'updateUjiFungsiVerification'])->name('ujifungsi-verification');
    Route::POST('/document-verification/{id}', [App\Http\Controllers\VerificationRequest\API\APIVerificationRequestController::class, 'updateDocumentVerification'])->name('document-verification');
});
Route::middleware(['auth', 'roles:2,3'])->prefix('raised-issue')->name('raised-issue.')->group(function () {
    Route::get('/', [App\Http\Controllers\RaisedIssue\RaisedIssueController::class, 'index'])->name('index');
});
Route::middleware(['auth', 'roles:2,3'])->prefix('reported-incidents')->name('reported-incidents.')->group(function () {
    Route::get('/', function () {
        return view('reported-incidents.index');
    })->name('index');
});
Route::middleware(['auth', 'roles:2,3'])->prefix('import-data')->name('import-data.')->group(function () {
    Route::get('/', [App\Http\Controllers\ImportData\ImportDataController::class, 'index'])->name('index');
    Route::post('/import-puskesmas', [App\Http\Controllers\ImportData\ImportDataController::class, 'importPuskesmas'])->name('import.puskesmas');
    Route::get('/download-template', [App\Http\Controllers\ImportData\ImportDataController::class, 'downloadExcel'])->name('download.template');
});
Route::middleware(['auth', 'roles:2'])->prefix('master-puskesmas')->name('master-puskesmas.')->group(function () {
    Route::get('/', [App\Http\Controllers\Puskesmas\MasterPuskesmasController::class, 'index'])->name('index');
});
Route::middleware(['auth', 'roles:2,3'])->prefix('api-puskesmas')->name('api-puskesmas.')->group(function () {
    Route::get('/fetch', [App\Http\Controllers\Puskesmas\API\APIPuskesmasController::class, 'fetchData'])->name('fetch-data');
    Route::get('/provinces', [App\Http\Controllers\Puskesmas\API\APIPuskesmasController::class, 'fetchProvinces'])->name('provinces');
    Route::get('/regencies', [App\Http\Controllers\Puskesmas\API\APIPuskesmasController::class, 'fetchRegencies'])->name('regencies');
    Route::get('/districts', [App\Http\Controllers\Puskesmas\API\APIPuskesmasController::class, 'fetchDistricts'])->name('districts');
}); 
Route::middleware(['auth', 'roles:2'])->prefix('api-puskesmas')->name('api-puskesmas.')->group(function () {
    Route::post('/store', [App\Http\Controllers\Puskesmas\API\APIPuskesmasController::class, 'store'])->name('store');
    Route::put('/{id}/update-basic', [App\Http\Controllers\Puskesmas\API\APIPuskesmasController::class, 'updateBasic'])->name('update-basic');
    Route::get('/test', [App\Http\Controllers\Puskesmas\API\APIPuskesmasController::class, 'testConnection'])->name('test');
});
Route::middleware(['auth', 'roles:2,3'])->get('/detail', function () {
        return view('detail');
    })->name('detail');
