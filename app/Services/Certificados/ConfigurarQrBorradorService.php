<?php

declare(strict_types=1);

namespace App\Services\Certificados;

use App\Enums\PresetQr;
use App\Models\Certificado;
use Illuminate\Support\Str;

final class ConfigurarQrBorradorService
{
    public function __construct(private readonly GeneradorPdfQr $generador) {}

    /**
     * Configura los parámetros del código QR para un certificado, genera el PDF borrador y los guarda.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \RuntimeException
     */
    public function ejecutar(Certificado $record, array $data): string
    {
        $defaults = config('certificados.defaults', []);
        $defaults = is_array($defaults) ? $defaults : [];

        $tokenBorrador = $record->getAttribute('token_borrador') ?: (string) Str::uuid();
        $usarManual = (bool) ($data['qr_manual'] ?? false);
        $presetValor = $usarManual
            ? PresetQr::Manual->value
            : (string) ($data['qr_preset_grid'] ?? $defaults['preset'] ?? PresetQr::Superior1->value);
        $preset = PresetQr::desdeValor($presetValor);
        $lado = is_numeric($data['qr_lado'] ?? null) ? (float) $data['qr_lado'] : null;
        $x = is_numeric($data['qr_x'] ?? null) ? (float) $data['qr_x'] : null;
        $y = is_numeric($data['qr_y'] ?? null) ? (float) $data['qr_y'] : null;
        $pagina = is_numeric($data['qr_pagina'] ?? null)
            ? max((int) $data['qr_pagina'], 1)
            : (int) ($defaults['pagina'] ?? 1);

        if ($preset !== PresetQr::Manual) {
            $x = null;
            $y = null;
        }

        $datosQr = array_filter([
            'preset' => $preset->value,
            'lado' => $lado,
            'x' => $x,
            'y' => $y,
        ], static fn ($valor): bool => $valor !== null);

        $record->forceFill([
            'datos_qr' => $datosQr,
            'qr_pagina' => $pagina,
            'token_borrador' => $tokenBorrador,
        ]);

        $rutaBorrador = $this->generador->generarBorrador($record, $tokenBorrador);

        $record->forceFill([
            'ruta_pdf_borrador' => $rutaBorrador,
        ])->save();

        return $rutaBorrador;
    }
}
