<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemandaController;
use App\Http\Controllers\ProjetoController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Login Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Demandas Routes - Todos os usuários autenticados podem acessar
    Route::resource('demandas', DemandaController::class);
    Route::post('demandas/{demanda}/homologar', [DemandaController::class, 'homologar'])->name('demandas.homologar');
    Route::get('demandas-exportar', [DemandaController::class, 'exportarPdf'])->name('demandas.exportar');
    Route::post('demandas/{demanda}/arquivos', [DemandaController::class, 'uploadArquivo'])->name('demandas.arquivos.upload');
    Route::get('demandas/arquivos/{arquivo}/download', [DemandaController::class, 'downloadArquivo'])->name('demandas.arquivos.download');
    Route::get('demandas/arquivos/{arquivo}/view', [DemandaController::class, 'viewArquivo'])->name('demandas.arquivos.view');
    Route::delete('demandas/arquivos/{arquivo}', [DemandaController::class, 'deletarArquivo'])->name('demandas.arquivos.delete');

    // Rotas apenas para administradores
    Route::middleware('admin')->group(function () {
        // Usuários Routes
        Route::resource('users', UserController::class);

        // Clientes Routes
        Route::resource('clientes', ClienteController::class);

        // Projetos Routes
        Route::resource('projetos', ProjetoController::class);

        // Status Routes
        Route::resource('status', StatusController::class);
    });
});
