<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EnvironmentController;

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

Route::get('/', function () {
    return view('welcome');
});

// Route::middleware([
//     'auth:sanctum',
//     config('jetstream.auth_session'),
//     'verified'
// ])->group(function () {
//     Route::get('/dashboard', function () {
//         return view('dashboard');
//     })->name('dashboard');
// });

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->group(function () {
    Route::get('/certificates', [CertificateController::class, "index"]);
});


Route::get('/create-certificate',  [CertificateController::class, "create"]);//->middleware('auth');
Route::post('/create-certificate',  [CertificateController::class, "store"]);//->middleware('auth');

Route::get('/certificate-details/{certificate}', [CertificateController::class, "show"])->middleware('auth')->name('certificate-details');
Route::post('/certificate-details/{certificate}/store-domains', [CertificateController::class, "storeDomains"])->name('store-domains');//->middleware('auth')->name('store-domains');
Route::post('/certificate-details/{certificate}/delete-domains', [CertificateController::class, "deleteDomains"])->name('delete-domains');//->middleware('auth')->name('store-domains');
Route::get('/certificate-delete/{id}', [CertificateController::class, "destroy"])->middleware('auth')->name('certificate-delete');
Route::get('/certificate-activate/{id}', [CertificateController::class, "activate"])->middleware('auth')->name('certificate-activate');
Route::get('/certificate-deactivate/{id}', [CertificateController::class, "deactivate"])->middleware('auth')->name('certificate-deactivate');
Route::post('/certificate-install', [CertificateController::class, "addCertificateToEnvironment"])->middleware('auth');

Route::get('/domain-details', function () {
    return view('domain-details');
})->middleware('auth');



Route::get('/users', [UserController::class, "index"])->middleware('auth');



Route::get('/environments', [EnvironmentController::class, "index"])->middleware('auth');
Route::get('/environments/certificate', [EnvironmentController::class, "getCertificateID"])->middleware('auth');