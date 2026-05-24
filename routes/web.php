<?php

use App\Http\Controllers\DescargarCertificadoController;
use App\Http\Controllers\VerificacionCertificadoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/verificar/{certificado}', VerificacionCertificadoController::class)
    ->name('certificados.verificar');

Route::get('/certificados/{certificado}/descargar', DescargarCertificadoController::class)
    ->name('certificados.descargar');
