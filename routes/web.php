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



Route::get('/certificates', [CertificateController::class, "index"])->middleware('auth')->name('certificate');
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







Route::get('/environments', [EnvironmentController::class, "index"])->middleware('auth')->name('environment');
Route::get('/environments/certificate', [EnvironmentController::class, "getCertificateID"])->middleware('auth');


Route::middleware('auth')->group(function () {
    Route::get('/users', [UserController::class, "index"])->name('user');
    Route::get('/register-user', [UserController::class, "create"])->name('registration');
    Route::post('/register-user', [UserController::class, "store"])->name('registration');
    Route::get('/view-user/{id}', [UserController::class, "show"])->name('view-user');
    Route::get('/update-user/{id}', [UserController::class, "edit"])->name('edit-user');
    Route::post('/update-user/{id}', [UserController::class, "update"])->name('edit-user');
    Route::post('/delete-user', [UserController::class, "destroy"])->name('delete-user');
});