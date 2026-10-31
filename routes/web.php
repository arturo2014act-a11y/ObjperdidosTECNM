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

// Publicación de Objetos (RF-13, RF-18 a RF-20)
Route::get('/publicar', [ItemController::class, 'create'])->name('items.create');
Route::post('/publicar', [ItemController::class, 'store'])->name('items.store');
Route::get('/items/{item}', [ItemController::class, 'show'])->name('items.show');

// Mis Publicaciones y Solicitud de Cierre (RF-17, RF-24, RF-49)
Route::get('/mis-publicaciones', [ItemController::class, 'myItems'])->name('items.my-items');
Route::post('/items/{item}/request-closure', [ItemController::class, 'requestClosure'])->name('items.request-closure');

// Perfil de Usuario (RF-81 a RF-85)
Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
Route::put('/perfil/nombre', [ProfileController::class, 'updateName'])->name('profile.update-name');
Route::put('/perfil/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');

// Conmutador de Roles Institucionales con Correo (Para Pruebas del Prototipo)
Route::post('/switch-role', [RoleSwitchController::class, 'switchRole'])->name('switch-role');
Route::post('/register', [RegisteredUserController::class, 'store'])->name('register');

// Panel de Gestión de Prefectura (RF-50 a RF-56)
Route::prefix('prefectura')->name('prefectura.')->group(function () {
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

// Panel de Administración de Cuentas (RF-27, RF-86 a RF-91)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/cuentas', [AdminController::class, 'index'])->name('users.index');
    Route::get('/cuentas/{user}', [AdminController::class, 'show'])->name('users.show');
    Route::post('/cuentas/{user}/desactivar', [AdminController::class, 'deactivate'])->name('users.deactivate');
    Route::post('/cuentas/{user}/reactivar', [AdminController::class, 'reactivate'])->name('users.reactivate');
});
