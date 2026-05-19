<?php

declare(strict_types=1);

namespace App\Services\Certificados;

use App\Enums\EstadoCertificado;

final class AdjuntarFirmadoResultado
{
    public function __construct(
        public readonly EstadoCertificado $estado,
        public readonly bool $borradorCoincide,
        public readonly string $titulo,
        public readonly string $mensaje,
        public readonly string $color,
    ) {}
}
