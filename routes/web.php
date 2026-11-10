<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PrefecturaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleSwitchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Objetos Perdidos y Encontrados TecNM Mérida (Campus Poniente)
|--------------------------------------------------------------------------
*/

// Muro Institucional Principal (RF-12)
Route::get('/', [ItemController::class, 'index'])->name('home');
Route::get('/muro', [ItemController::class, 'index'])->name('items.index');

// Vista pública de un objeto (sin login requerido)
Route::get('/items/{item}', [ItemController::class, 'show'])->name('items.show');

// Rutas protegidas: requieren login + correo verificado (RF-10, RF-68)
Route::middleware(['auth', 'verified'])->group(function () {
    // Publicación de Objetos (RF-13, RF-18 a RF-20)
    Route::get('/publicar', [ItemController::class, 'create'])->name('items.create');
    Route::post('/publicar', [ItemController::class, 'store'])->name('items.store');

    // Mis Publicaciones y Solicitud de Cierre (RF-17, RF-24, RF-49)
    Route::get('/mis-publicaciones', [ItemController::class, 'myItems'])->name('items.my-items');
    Route::post('/items/{item}/request-closure', [ItemController::class, 'requestClosure'])->name('items.request-closure');

    // Perfil de Usuario (RF-81 a RF-85)
    Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/perfil/nombre', [ProfileController::class, 'updateName'])->name('profile.update-name');
    Route::put('/perfil/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
});

// Autenticación Institucional (RF-09, RF-10, RF-63)
Route::middleware('guest')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store']);
    Route::get('/registro', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/registro', [RegisteredUserController::class, 'store']);

    // Recuperación de Contraseña (RF-28, RF-71, RF-72, RF-76, RF-77)
    Route::get('/recuperar-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'create'])->name('password.request');
    Route::post('/recuperar-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'store'])->name('password.email');
    Route::get('/restablecer-password/{token}', [\App\Http\Controllers\Auth\PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/restablecer-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'update'])->name('password.update');
});

// Cierre de Sesión (RF-63)
Route::post('/logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');

// Verificación de Correo (RF-10, RF-68, RF-69, RF-70)
Route::middleware('auth')->group(function () {
    Route::get('/verificar-correo', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'prompt'])->name('verification.notice');
    Route::get('/verificar-correo/{id}/{hash}', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/verificar-correo/reenviar', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

// Conmutador de Roles Institucionales con Correo (Para Pruebas del Prototipo)
Route::post('/switch-role', [RoleSwitchController::class, 'switchRole'])->name('switch-role');

// Panel de Gestión de Prefectura — requiere login + correo verificado (RF-50 a RF-56)
Route::middleware(['auth', 'verified', 'role:prefectura'])->prefix('prefectura')->name('prefectura.')->group(function () {
    Route::get('/dashboard', [PrefecturaController::class, 'dashboard'])->name('dashboard');

    // Sección 1: Encontrados pendientes de aprobación (RF-50, RF-53, RF-21)
    Route::post('/items/{item}/approve-found', [PrefecturaController::class, 'approveFound'])->name('items.approve-found');
    Route::post('/items/{item}/reject-found', [PrefecturaController::class, 'rejectFound'])->name('items.reject-found');

    // Sección 2: Solicitudes de cierre pendientes (RF-50, RF-48)
    Route::post('/items/{item}/approve-closure', [PrefecturaController::class, 'approveClosure'])->name('items.approve-closure');
    Route::post('/items/{item}/reject-closure', [PrefecturaController::class, 'rejectClosure'])->name('items.reject-closure');

    // Sección 3: Publicaciones bajo gestión (RF-51, RF-14, RF-54, RF-55)
    Route::post('/items/{item}/mark-returned', [PrefecturaController::class, 'markReturned'])->name('items.mark-returned');
    Route::post('/items/{item}/revert-returned', [PrefecturaController::class, 'revertReturned'])->name('items.revert-returned');
});

// Panel de Administración de Cuentas — requiere login + correo verificado (RF-27, RF-86 a RF-91)
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/cuentas', [AdminController::class, 'index'])->name('users.index');
    Route::get('/cuentas/{user}', [AdminController::class, 'show'])->name('users.show');
    Route::post('/cuentas/{user}/desactivar', [AdminController::class, 'deactivate'])->name('users.deactivate');
    Route::post('/cuentas/{user}/reactivar', [AdminController::class, 'reactivate'])->name('users.reactivate');
});
