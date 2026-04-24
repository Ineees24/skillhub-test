<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FormationController;
use App\Http\Controllers\AtelierController;

// Routes publiques
Route::post('/login',    [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);


Route::get('/ateliers', [AtelierController::class, 'liste']);
Route::get('/ateliers/{id}', [AtelierController::class, 'detail']);
Route::post('/activity-log', [AtelierController::class, 'logActivity']);

// Routes protégées
Route::middleware('spring.auth')->group(function () {

    // Route pour récupérer l’utilisateur connecté
    Route::get('/me',       [AuthController::class, 'me']);
    // Route pour se déconnecter (invalider le token)
    Route::post('/logout',  [AuthController::class, 'logout']);

    // Formations du formateur connecté
    Route::get('/formations',       [FormationController::class, 'index']);
    Route::post('/formations',      [FormationController::class, 'store']);
    Route::delete('/formations/{id}', [FormationController::class, 'destroy']);
    Route::put('/formations/{id}', [FormationController::class, 'update']);
    Route::get('/formations/{id}/modules', [FormationController::class, 'modulesIndex']);
    Route::post('/formations/{id}/modules', [FormationController::class, 'modulesStore']);

    // Catégories
    Route::get('/categories', [FormationController::class, 'categories']);

    // Journaux d'activité MongoDB (utilisateur connecté)
    Route::get('/activity-logs', [AtelierController::class, 'activityLogs']);
    Route::get('/mes-inscriptions', [AtelierController::class, 'mesInscriptions']);

    // Inscription apprenant à une formation
    Route::post('/ateliers/{id}/inscription', [AtelierController::class, 'inscrire']);
    Route::delete('/ateliers/{id}/inscription', [AtelierController::class, 'desinscrire']);
});