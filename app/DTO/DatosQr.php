<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enums\PresetQr;
use App\Models\Certificado;

final readonly class DatosQr
{
    public function __construct(
        public PresetQr $preset,
        public float $lado,
        public ?float $x,
        public ?float $y,
        public float $margenX,
        public float $margenY,
        public float $anchoBloqueFactor,
        public int $pagina,
    ) {}

    /**
     * @param  array<string, mixed>  $defaults
     */
    public static function desdeRegistro(Certificado $certificado, array $defaults): self
    {
        $datosQr = $certificado->getAttribute('datos_qr');
        $datosQr = is_array($datosQr) ? $datosQr : [];

        $preset = PresetQr::desdeValor((string) ($datosQr['preset'] ?? $defaults['preset'] ?? PresetQr::Superior1->value));

        $lado = self::normalizarFloat($datosQr['lado'] ?? $defaults['lado'] ?? 30.0, 30.0);
        $x = self::normalizarFloatOpcional($datosQr['x'] ?? $defaults['x'] ?? null);
        $y = self::normalizarFloatOpcional($datosQr['y'] ?? $defaults['y'] ?? null);

        $margenX = self::normalizarFloat($defaults['margen_x'] ?? 5.0, 5.0);
        $margenY = self::normalizarFloat($defaults['margen_y'] ?? 5.0, 5.0);
        $anchoBloqueFactor = self::normalizarFloat($defaults['ancho_bloque_factor'] ?? 1.2, 1.2);

        $paginaRegistro = $certificado->getAttribute('qr_pagina');
        $pagina = self::normalizarInt($paginaRegistro ?? $defaults['pagina'] ?? 1, 1);

        return new self(
            preset: $preset,
            lado: $lado,
            x: $x,
            y: $y,
            margenX: $margenX,
            margenY: $margenY,
            anchoBloqueFactor: $anchoBloqueFactor,
            pagina: $pagina,
        );
    }

    private static function normalizarFloat(mixed $valor, float $porDefecto): float
    {
        return is_numeric($valor) ? (float) $valor : $porDefecto;
    }

    private static function normalizarFloatOpcional(mixed $valor): ?float
    {
        return is_numeric($valor) ? (float) $valor : null;
    }

    private static function normalizarInt(mixed $valor, int $porDefecto): int
    {
        if (! is_numeric($valor)) {
            return $porDefecto;
        }

        $valor = (int) $valor;

        return $valor < 1 ? 1 : $valor;
    }
}
