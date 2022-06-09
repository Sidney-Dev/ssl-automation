<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EnvironmentController;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;

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

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});


Route::get('/domain-details', function () {
    return view('domain-details');
})->middleware('auth');


Route::middleware('auth')->group(function () {

    // Environments Controller route
    Route::get('/environments', [EnvironmentController::class, "index"])->name('environment');
    Route::get('/environments/certificate', [EnvironmentController::class, "getCertificateID"]);

    // Certificates Controller route
    Route::get('/certificates', [CertificateController::class, "index"])->name('certificate');
    Route::get('/create-certificate',  [CertificateController::class, "create"]);
    Route::post('/create-certificate',  [CertificateController::class, "store"]);
    Route::get('/certificate-details/{certificate}', [CertificateController::class, "show"])->name('certificate-details');
    Route::post('/certificate-details/{certificate}/store-domains', [CertificateController::class, "storeDomains"])->name('store-domains');
    Route::get('/certificate-remove/{id}', [CertificateController::class, "destroy"])->name('certificate-remove');
    Route::post('/certificate-delete', [CertificateController::class, "delete"])->name('certificate-delete');
    Route::get('/certificate-activate/{id}', [CertificateController::class, "activate"])->name('certificate-activate');
    Route::get('/certificate-deactivate/{id}', [CertificateController::class, "deactivate"])->name('certificate-deactivate');
    Route::post('/certificate-install', [CertificateController::class, "addCertificateToEnvironment"]);

    // Users Controller route
    Route::get('/users', [UserController::class, "index"])->name('user');
    Route::get('/register-user', [UserController::class, "create"])->name('registration');
    Route::post('/register-user', [UserController::class, "store"])->name('registration');
    Route::get('/view-user/{id}', [UserController::class, "show"])->name('view-user');
    Route::get('/update-user/{id}', [UserController::class, "edit"])->name('edit-user');
    Route::post('/update-user/{id}', [UserController::class, "update"])->name('edit-user');
    Route::post('/delete-user', [UserController::class, "destroy"])->name('delete-user');
});