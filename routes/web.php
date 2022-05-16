<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\CertificateController;

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
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/certificates', [CertificateController::class, "index"]);
    
    Route::get('/create-certificate',  [CertificateController::class, "create"]);

    Route::post('/create-certificate',  [CertificateController::class, "store"]);
    
    Route::get('/certificate-details/{id}', [CertificateController::class, "show"])->name('certificate-details');
    
    Route::get('/domain-details', function () {
        return view('domain-details');
    });
    
    
    Route::get('/environments', function () {
        return view('environments');
    });
    
    
    Route::get('/users', function () {
        return view('users');
    });
});
