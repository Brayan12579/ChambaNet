<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\EmployerController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/empleos', [PublicController::class, 'index'])->name('jobs.index');
Route::get('/empleos/{job:slug}', [PublicController::class, 'show'])->name('jobs.show');
Route::get('/empresas', [PublicController::class, 'companies'])->name('companies.index');
Route::get('/empresas/{company:slug}/logo', [PublicController::class, 'logo'])->name('companies.logo');
Route::get('/empresas/{company:slug}', [PublicController::class, 'company'])->name('companies.show');
Route::get('/recursos', [PublicController::class, 'resources'])->name('resources.index');
Route::get('/recursos/{resource:slug}', [PublicController::class, 'resource'])->name('resources.show');

Route::middleware('guest')->controller(AuthController::class)->group(function (): void {
    Route::get('/iniciar-sesion', 'login')->name('login');
    Route::post('/iniciar-sesion', 'authenticate')->middleware('throttle:6,1')->name('login.store');
    Route::get('/registro', 'register')->name('register');
    Route::post('/registro', 'store')->middleware('throttle:10,1')->name('register.store');
    Route::get('/recuperar-clave', 'forgot')->name('password.request');
    Route::post('/recuperar-clave', 'sendReset')->middleware('throttle:3,1')->name('password.email');
    Route::get('/restablecer-clave/{token}', 'reset')->name('password.reset');
    Route::post('/restablecer-clave', 'updatePassword')->middleware('throttle:6,1')->name('password.update');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/cerrar-sesion', [AuthController::class, 'logout'])->name('logout');
    Route::middleware('role:candidate,employer,admin')->group(function (): void {
        Route::get('/panel', [AuthController::class, 'dashboard'])->name('dashboard');
        Route::get('/notificaciones', [NotificationController::class, 'index'])->name('notifications.index');
        Route::patch('/notificaciones/leer-todas', [NotificationController::class, 'readAll'])->name('notifications.read-all');
        Route::patch('/notificaciones/{notification}/leer', [NotificationController::class, 'read'])->whereUuid('notification')->name('notifications.read');
        Route::get('/postulaciones/{application}/cv', [ApplicationController::class, 'cv'])->name('applications.cv');
        Route::post('/empleos/{job}/reportar', [ReportController::class, 'store'])->middleware('throttle:5,1')->name('reports.store');
    });

    Route::middleware('role:candidate')->group(function (): void {
        Route::prefix('candidato')->name('candidate.')->group(function (): void {
            Route::get('/', [CandidateController::class, 'dashboard'])->name('dashboard');
            Route::get('/perfil', [CandidateController::class, 'edit'])->name('profile.edit');
            Route::put('/perfil', [CandidateController::class, 'update'])->name('profile.update');
            Route::get('/cv', [CandidateController::class, 'cv'])->name('cv');
            Route::get('/postulaciones', [ApplicationController::class, 'index'])->name('applications.index');
            Route::patch('/postulaciones/{application}/retirar', [ApplicationController::class, 'withdraw'])->name('applications.withdraw');
            Route::get('/guardados', [FavoriteController::class, 'index'])->name('favorites.index');
        });
        Route::post('/empleos/{job}/guardar', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
        Route::post('/empleos/{job}/postular', [ApplicationController::class, 'store'])->middleware('throttle:15,1')->name('applications.store');
    });

    Route::prefix('empleador')->name('employer.')->middleware('role:employer')->controller(EmployerController::class)->group(function (): void {
        Route::get('/', 'dashboard')->name('dashboard');
        Route::get('/empresa', 'company')->name('company.edit');
        Route::put('/empresa', 'updateCompany')->name('company.update');
        Route::get('/ofertas', 'index')->name('jobs.index');
        Route::get('/ofertas/crear', 'create')->name('jobs.create');
        Route::post('/ofertas', 'store')->name('jobs.store');
        Route::get('/ofertas/{job}/editar', 'edit')->name('jobs.edit');
        Route::put('/ofertas/{job}', 'update')->name('jobs.update');
        Route::patch('/ofertas/{job}/cerrar', 'close')->name('jobs.close');
        Route::delete('/ofertas/{job}', 'destroy')->name('jobs.destroy');
        Route::get('/ofertas/{job}/postulantes', 'applicants')->name('jobs.applicants');
        Route::patch('/postulaciones/{application}', 'updateApplication')->name('applications.update');
    });

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->controller(AdminController::class)->group(function (): void {
        Route::get('/', 'dashboard')->name('dashboard');
        Route::get('/postulaciones', 'applications')->name('applications');
        Route::patch('/empresas/{company}/verificar', 'verifyCompany')->name('companies.verify');
        Route::patch('/ofertas/{job}/cerrar', 'closeJob')->name('jobs.close');
        Route::get('/gestion/{section}', 'index')->name('content.index');
        Route::get('/gestion/{section}/crear', 'create')->name('content.create');
        Route::post('/gestion/{section}', 'store')->name('content.store');
        Route::get('/gestion/{section}/{record}/editar', 'edit')->whereNumber('record')->name('content.edit');
        Route::put('/gestion/{section}/{record}', 'update')->whereNumber('record')->name('content.update');
        Route::delete('/gestion/{section}/{record}', 'destroy')->whereNumber('record')->name('content.destroy');
    });
});
