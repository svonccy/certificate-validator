<?php

declare(strict_types=1);

namespace App\Services\Certificados;

use App\DTO\DatosQr;
use App\DTO\PosicionQr;
use App\Enums\PresetQr;

final class CalculadorPosicionQr
{
    /**
     * @param array{width: float, height: float} $tamano
     */
    public function calcular(DatosQr $datosQr, array $tamano, float $gapTexto, float $altoTexto): PosicionQr
    {
        $lado = $datosQr->lado;
        $anchoBloque = max($lado, $lado * $datosQr->anchoBloqueFactor);
        $altoBloque = $lado + $gapTexto + $altoTexto;

        [$x, $y] = $this->calcularBase($datosQr, $tamano, $anchoBloque, $altoBloque);

        $x = $this->ajustarDentro($x, $tamano['width'] - $anchoBloque);
        $y = $this->ajustarDentro($y, $tamano['height'] - $altoBloque);

        return new PosicionQr(
            x: $x,
            y: $y,
            lado: $lado,
            anchoBloque: $anchoBloque,
            altoBloque: $altoBloque,
        );
    }

    /**
     * @param array{width: float, height: float} $tamano
     * @return array{0: float, 1: float}
     */
    private function calcularBase(DatosQr $datosQr, array $tamano, float $anchoBloque, float $altoBloque): array
    {
        return match ($datosQr->preset) {
            PresetQr::SuperiorIzquierda => [$datosQr->margenX, $datosQr->margenY],
            PresetQr::SuperiorDerecha => [$tamano['width'] - $anchoBloque - $datosQr->margenX, $datosQr->margenY],
            PresetQr::InferiorIzquierda => [$datosQr->margenX, $tamano['height'] - $altoBloque - $datosQr->margenY],
            PresetQr::InferiorDerecha => [$tamano['width'] - $anchoBloque - $datosQr->margenX, $tamano['height'] - $altoBloque - $datosQr->margenY],
            PresetQr::Centro => [($tamano['width'] - $anchoBloque) / 2, ($tamano['height'] - $altoBloque) / 2],
            PresetQr::Manual => [$datosQr->x ?? $datosQr->margenX, $datosQr->y ?? $datosQr->margenY],
        };
    }

    private function ajustarDentro(float $valor, float $maximo): float
    {
        $maximo = max(0.0, $maximo);

        if ($valor < 0.0) {
            return 0.0;
        }

        if ($valor > $maximo) {
            return $maximo;
        }

        return $valor;
    }
}
