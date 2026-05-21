<?php

declare(strict_types=1);

use App\DTO\DatosQr;
use App\Enums\PresetQr;
use App\Services\Certificados\CalculadorPosicionQr;

it('calcula la posicion superior 1 con margen', function () {
    $datosQr = new DatosQr(
        preset: PresetQr::Superior1,
        lado: 30.0,
        x: null,
        y: null,
        margenX: 5.0,
        margenY: 7.0,
        anchoBloqueFactor: 1.2,
        pagina: 1,
    );

    $tamano = ['width' => 200.0, 'height' => 100.0];

    $posicion = (new CalculadorPosicionQr)->calcular($datosQr, $tamano, 1.0, 12.0);

    expect($posicion->xQr)->toBe(8.0)
        ->and($posicion->yQr)->toBe(7.0)
        ->and($posicion->textoArriba)->toBeFalse();
});

it('calcula la posicion inferior 5 con bloque completo', function () {
    $datosQr = new DatosQr(
        preset: PresetQr::Inferior5,
        lado: 30.0,
        x: null,
        y: null,
        margenX: 5.0,
        margenY: 7.0,
        anchoBloqueFactor: 1.2,
        pagina: 1,
    );

    $tamano = ['width' => 200.0, 'height' => 100.0];

    $posicion = (new CalculadorPosicionQr)->calcular($datosQr, $tamano, 1.0, 12.0);

    expect($posicion->xQr)->toBe(162.0)
        ->and($posicion->yQr)->toBe(63.0)
        ->and($posicion->textoArriba)->toBeTrue();
});

it('ajusta coordenadas manuales fuera del rango', function () {
    $datosQr = new DatosQr(
        preset: PresetQr::Manual,
        lado: 30.0,
        x: 500.0,
        y: -10.0,
        margenX: 0.0,
        margenY: 0.0,
        anchoBloqueFactor: 1.2,
        pagina: 1,
    );

    $tamano = ['width' => 100.0, 'height' => 80.0];

    $posicion = (new CalculadorPosicionQr)->calcular($datosQr, $tamano, 1.0, 12.0);

    expect($posicion->xQr)->toBe(67.0)
        ->and($posicion->yQr)->toBe(0.0)
        ->and($posicion->textoArriba)->toBeFalse();
});
