<?php

declare(strict_types=1);

use App\DTO\DatosQr;
use App\Enums\PresetQr;
use App\Services\Certificados\CalculadorPosicionQr;

it('calcula la posicion superior izquierda con margen', function () {
    $datosQr = new DatosQr(
        preset: PresetQr::SuperiorIzquierda,
        lado: 30.0,
        x: null,
        y: null,
        margenX: 5.0,
        margenY: 7.0,
        anchoBloqueFactor: 1.2,
        pagina: 1,
    );

    $tamano = ['width' => 200.0, 'height' => 100.0];

    $posicion = (new CalculadorPosicionQr())->calcular($datosQr, $tamano, 1.0, 12.0);

    expect($posicion->x)->toBe(5.0)
        ->and($posicion->y)->toBe(7.0);
});

it('calcula la posicion inferior derecha con bloque completo', function () {
    $datosQr = new DatosQr(
        preset: PresetQr::InferiorDerecha,
        lado: 30.0,
        x: null,
        y: null,
        margenX: 5.0,
        margenY: 7.0,
        anchoBloqueFactor: 1.2,
        pagina: 1,
    );

    $tamano = ['width' => 200.0, 'height' => 100.0];

    $posicion = (new CalculadorPosicionQr())->calcular($datosQr, $tamano, 1.0, 12.0);

    expect($posicion->x)->toBe(159.0)
        ->and($posicion->y)->toBe(50.0);
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

    $posicion = (new CalculadorPosicionQr())->calcular($datosQr, $tamano, 1.0, 12.0);

    expect($posicion->x)->toBe(64.0)
        ->and($posicion->y)->toBe(0.0);
});
