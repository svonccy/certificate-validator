<?php

declare(strict_types=1);

namespace App\Services\Certificados;

interface EditorPdfContract
{
    /**
     * Carga el archivo PDF de origen y devuelve el número total de páginas.
     *
     * @throws \RuntimeException
     */
    public function cargarOrigen(string $rutaOriginal): int;

    /**
     * Agrega metadatos (como keywords) al documento.
     */
    public function establecerKeywords(string $keywords): void;

    /**
     * Importa y agrega una página del documento original manteniendo su tamaño y orientación.
     * Devuelve las dimensiones de la página: ['width' => float, 'height' => float, 'orientation' => string]
     */
    public function clonarPagina(int $paginaOriginal): array;

    /**
     * Dibuja un código QR en la página actual.
     */
    public function dibujarQr(string $contenido, float $x, float $y, float $lado): void;

    /**
     * Escribe un texto centrado en la página actual.
     */
    public function escribirTextoCentrado(
        string $texto,
        float $x,
        float $y,
        float $ancho,
        float $alto,
        string $fuente = 'helvetica',
        int $tamano = 7
    ): void;

    /**
     * Guarda el PDF resultante en la ruta especificada.
     */
    public function guardarEn(string $rutaDestino): void;
}
