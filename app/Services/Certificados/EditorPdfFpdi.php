<?php

declare(strict_types=1);

namespace App\Services\Certificados;

use setasign\Fpdi\Tcpdf\Fpdi;

final class EditorPdfFpdi implements EditorPdfContract
{
    private readonly Fpdi $pdf;

    public function __construct()
    {
        $this->pdf = new Fpdi;
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);
        $this->pdf->SetMargins(0, 0, 0, true);
        $this->pdf->SetAutoPageBreak(false);
    }

    public function cargarOrigen(string $rutaOriginal): int
    {
        return (int) $this->pdf->setSourceFile($rutaOriginal);
    }

    public function establecerKeywords(string $keywords): void
    {
        $this->pdf->SetKeywords($keywords);
    }

    public function clonarPagina(int $paginaOriginal): array
    {
        $paginaId = $this->pdf->importPage($paginaOriginal);
        $tamano = $this->pdf->getTemplateSize($paginaId);

        $width = (float) $tamano['width'];
        $height = (float) $tamano['height'];
        $orientation = (string) $tamano['orientation'];

        $this->pdf->AddPage($orientation, [$width, $height]);
        $this->pdf->useTemplate($paginaId);

        return [
            'width' => $width,
            'height' => $height,
            'orientation' => $orientation,
        ];
    }

    public function dibujarQr(string $contenido, float $x, float $y, float $lado): void
    {
        $estiloQr = [
            'border' => 0,
            'padding' => 5,
            'fgcolor' => [0, 0, 0],
            'bgcolor' => [255, 255, 255],
        ];

        $this->pdf->write2DBarcode($contenido, 'QRCODE,H', $x, $y, $lado, $lado, $estiloQr, 'N');
    }

    public function escribirTextoCentrado(
        string $texto,
        float $x,
        float $y,
        float $ancho,
        float $alto,
        string $fuente = 'helvetica',
        int $tamano = 7
    ): void {
        $this->pdf->SetTextColor(0);
        $this->pdf->SetFont($fuente, '', $tamano);
        $this->pdf->SetXY($x, $y);
        $this->pdf->Cell($ancho, $alto, $texto, 0, 1, 'C');
    }

    public function guardarEn(string $rutaDestino): void
    {
        $this->pdf->Output($rutaDestino, 'F');
    }
}
