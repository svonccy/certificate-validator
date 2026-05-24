<?php

declare(strict_types=1);

use App\Models\FirmaConfianza;
use App\Services\Certificados\ParserResultadoFirma;
use App\Services\Certificados\ValidadorFirmaPdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('retorna trust roots vacio si verificar_cadena_confianza es false', function () {
    Config::set('certificados.verificar_cadena_confianza', false);

    // Creamos una firma de confianza activa
    FirmaConfianza::query()->create([
        'nombre' => 'Test Autoridad',
        'ruta_firma' => 'firmas/test.crt',
        'activo' => true,
    ]);

    $parser = new ParserResultadoFirma;
    $validador = new ValidadorFirmaPdf($parser);

    $reflector = new ReflectionClass(ValidadorFirmaPdf::class);
    $metodo = $reflector->getMethod('obtenerTrustRoots');
    $metodo->setAccessible(true);

    $resultados = $metodo->invoke($validador);

    expect($resultados)->toBeEmpty();
});

it('retorna trust roots si verificar_cadena_confianza es true y existen archivos', function () {
    Config::set('certificados.verificar_cadena_confianza', true);
    Config::set('certificados.disk', 'local');

    Storage::fake('local');
    Storage::disk('local')->put('firmas/test.crt', 'contenido-certificado');

    FirmaConfianza::query()->create([
        'nombre' => 'Test Autoridad',
        'ruta_firma' => 'firmas/test.crt',
        'activo' => true,
    ]);

    $parser = new ParserResultadoFirma;
    $validador = new ValidadorFirmaPdf($parser);

    $reflector = new ReflectionClass(ValidadorFirmaPdf::class);
    $metodo = $reflector->getMethod('obtenerTrustRoots');
    $metodo->setAccessible(true);

    $resultados = $metodo->invoke($validador);

    expect($resultados)->not->toBeEmpty()
        ->and($resultados[0])->toContain('firmas/test.crt');
});
