<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrabajadorController;

// Rutas de autenticación
Route::post('/login', [AuthController::class, 'login']);

/**
 * Grupo de rutas protegidas por JWT (autenticación)
 * Las rutas dentro de este grupo solo son accesibles para usuarios autenticados
 */
Route::middleware(['jwt.auth'])->group(function () {
    
    /**
     * Ruta para crear un nuevo trabajador
     * Esta ruta solo puede ser accesible por usuarios autenticados
     */
    Route::post('/trabajadores', [TrabajadorController::class, 'store']);
    
    /**
     * Ruta para actualizar un trabajador existente
     * Esta ruta solo puede ser accesible por usuarios autenticados
     */
    Route::post('/trabajadores/{id}', [TrabajadorController::class, 'update']);
    
    /**
     * Ruta para obtener la lista de trabajadores
     * Esta ruta solo puede ser accesible por usuarios autenticados
     */
    Route::get('/trabajadoresLista', [TrabajadorController::class, 'Trabajadores']);
    
    /**
     * Ruta para obtener la lista de puestos laborales
     * Esta ruta solo puede ser accesible por usuarios autenticados
     */
    Route::get('/positions', [TrabajadorController::class, 'PuestosLaborales']);
});
