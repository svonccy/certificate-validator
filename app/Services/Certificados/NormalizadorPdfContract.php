<?php

declare(strict_types=1);

namespace App\Services\Certificados;

interface NormalizadorPdfContract
{
    /**
     * Repara un archivo PDF si es incompatible con las librerías de lectura nativas.
     * Lanza una RuntimeException si no se puede reparar.
     *
     * @throws \RuntimeException
     */
    public function normalizar(string $rutaPdf): void;
}
