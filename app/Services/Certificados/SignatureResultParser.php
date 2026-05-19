<?php

declare(strict_types=1);

namespace App\Services\Certificados;

use RuntimeException;

final class SignatureResultParser
{
    /**
     * @return array<string, mixed>
     */
    public function parse(string $salida): array
    {
        $salida = trim($salida);

        if ($salida === '') {
            throw new RuntimeException('El validador no devolvio respuesta.');
        }

        $resultado = json_decode($salida, true);

        if (! is_array($resultado)) {
            throw new RuntimeException('Respuesta invalida del validador.');
        }

        return $resultado;
    }
}
