<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EstadoCertificado: string implements HasColor, HasLabel
{
    case PdfNoEncontrado = 'PDF_NO_ENCONTRADO';
    case PendienteQr = 'PENDIENTE_QR';
    case PendienteFirma = 'PENDIENTE_FIRMA';
    case Firmado = 'FIRMADO';
    case Rechazado = 'RECHAZADO';

    public function getLabel(): string
    {
        return match ($this) {
            self::PdfNoEncontrado => 'PDF no encontrado',
            self::PendienteQr => 'Pendiente de QR',
            self::PendienteFirma => 'QR Incrustado',
            self::Firmado => 'Firmado',
            self::Rechazado => 'Rechazado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PdfNoEncontrado => 'info',
            self::PendienteQr => 'gray',
            self::PendienteFirma => 'warning',
            self::Firmado => 'success',
            self::Rechazado => 'danger',
        };
    }
}
