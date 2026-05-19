<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EstadoCertificado: string implements HasColor, HasLabel
{
    case Pendiente = 'PENDIENTE';
    case Valido = 'VALIDO';
    case Rechazado = 'RECHAZADO';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Valido => 'Válido',
            self::Rechazado => 'Rechazado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pendiente => 'warning',
            self::Valido => 'success',
            self::Rechazado => 'danger',
        };
    }
}
