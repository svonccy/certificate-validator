<?php

use App\Http\Controllers\DescargarCertificadoController;
use App\Http\Controllers\VerificacionCertificadoController;
use Illuminate\Support\Facades\Route;

// La ruta raíz '/' ahora está manejada por Filament (AdminPanelProvider)

Route::get('/verificar/{certificado}', VerificacionCertificadoController::class)
    ->name('certificados.verificar');

Route::get('/certificados/{certificado}/descargar', DescargarCertificadoController::class)
    ->name('certificados.descargar');
