<?php

declare(strict_types=1);

namespace App\Services\Certificados;

use App\DTO\DatosQr;
use App\DTO\PosicionQr;
use App\Enums\PresetQr;

final class CalculadorPosicionQr
{
    private const FILAS = 3;

    private const COLUMNAS = 5;

    /**
     * @param  array{width: float, height: float}  $tamano
     */
    public function calcular(DatosQr $datosQr, array $tamano, float $gapTexto, float $altoTexto): PosicionQr
    {
        $lado = $datosQr->lado;
        $anchoBloque = max($lado, $lado * $datosQr->anchoBloqueFactor);
        $altoBloque = $lado + $gapTexto + $altoTexto;
        $preset = $datosQr->preset->normalizado();

        $textoArriba = $this->resolverTextoArriba($preset, $datosQr, $tamano, $gapTexto, $altoTexto);
        $offsetTexto = $textoArriba ? ($gapTexto + $altoTexto) : 0.0;

        if ($preset === PresetQr::Manual) {
            $xQr = $datosQr->x ?? $datosQr->margenX;
            $yQr = $datosQr->y ?? $datosQr->margenY;

            $xBloque = $xQr - (($anchoBloque - $lado) / 2);
            $yBloque = $yQr - $offsetTexto;
        } else {
            [$fila, $columna] = $this->obtenerCoordenadasCuadricula($preset);

            $xBloque = $this->calcularPosicion(
                $tamano['width'],
                $anchoBloque,
                $datosQr->margenX,
                $columna,
                self::COLUMNAS,
            );
            $yBloque = $this->calcularPosicion(
                $tamano['height'],
                $altoBloque,
                $datosQr->margenY,
                $fila,
                self::FILAS,
            );
        }

        $xBloque = $this->ajustarDentro($xBloque, $tamano['width'] - $anchoBloque);
        $yBloque = $this->ajustarDentro($yBloque, $tamano['height'] - $altoBloque);

        $xQr = $xBloque + (($anchoBloque - $lado) / 2);
        $yQr = $yBloque + $offsetTexto;

        return new PosicionQr(
            xQr: $xQr,
            yQr: $yQr,
            lado: $lado,
            anchoBloque: $anchoBloque,
            altoBloque: $altoBloque,
            textoArriba: $textoArriba,
        );
    }

    private function resolverTextoArriba(PresetQr $preset, DatosQr $datosQr, array $tamano, float $gapTexto, float $altoTexto): bool
    {
        if ($preset === PresetQr::Manual) {
            $yQr = $datosQr->y ?? $datosQr->margenY;
            $alturaAbajo = $yQr + $datosQr->lado + $gapTexto + $altoTexto;

            return $alturaAbajo > ($tamano['height'] - $datosQr->margenY);
        }

        $coordenadas = $preset->coordenadasCuadricula();

        return is_array($coordenadas) && $coordenadas[0] === self::FILAS;
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function obtenerCoordenadasCuadricula(PresetQr $preset): array
    {
        $coordenadas = $preset->coordenadasCuadricula();

        return $coordenadas ?? [1, 1];
    }

    private function calcularPosicion(float $tamano, float $bloque, float $margen, int $indice, int $total): float
    {
        if ($total < 2) {
            return max(0.0, $margen);
        }

        $disponible = max(0.0, $tamano - $bloque - (2 * $margen));
        $paso = $disponible / ($total - 1);

        return $margen + (($indice - 1) * $paso);
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
