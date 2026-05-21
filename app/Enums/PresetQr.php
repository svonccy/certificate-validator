<?php

declare(strict_types=1);

namespace App\Enums;

enum PresetQr: string
{
    case Manual = 'manual';
    case SuperiorIzquierda = 'superior_izquierda';
    case SuperiorDerecha = 'superior_derecha';
    case InferiorIzquierda = 'inferior_izquierda';
    case InferiorDerecha = 'inferior_derecha';
    case Centro = 'centro';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Manual => 'Manual',
            self::SuperiorIzquierda => 'Esquina superior izquierda',
            self::SuperiorDerecha => 'Esquina superior derecha',
            self::InferiorIzquierda => 'Esquina inferior izquierda',
            self::InferiorDerecha => 'Esquina inferior derecha',
            self::Centro => 'Centro',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function opciones(): array
    {
        $opciones = [];

        foreach (self::cases() as $preset) {
            $opciones[$preset->value] = $preset->etiqueta();
        }

        return $opciones;
    }
}
