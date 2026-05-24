<?php

declare(strict_types=1);

namespace App\Services\Certificados;

interface ValidadorFirmaContract
{
    /**
     * @return array<string, mixed>
     */
    public function validar(string $rutaPdfFirmado, ?string $tokenBorrador = null): array;
}
