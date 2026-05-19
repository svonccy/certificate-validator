<?php

declare(strict_types=1);

namespace App\Services\Certificados;

use App\Models\Certificado;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use setasign\Fpdi\Tcpdf\Fpdi;

final class GeneradorPdfQr
{
    private const QR_LADO = 30.0;

    private const QR_ANCHO_BLOQUE = 36.0;

    private const QR_ALTO_TEXTO = 8.0;

    private const TEXTO_GAP = 1.0;

    private const QR_MARGEN_X = 5.0;

    private const QR_MARGEN_Y = 5.0;

    /**
     * @return array{width: float, height: float, orientation: string}
     */
    private function obtenerTamanoPlantilla(Fpdi $pdf, int|string $paginaId): array
    {
        $tamano = $pdf->getTemplateSize($paginaId);

        return [
            'width' => (float) $tamano['width'],
            'height' => (float) $tamano['height'],
            'orientation' => (string) $tamano['orientation'],
        ];
    }

    public function generarBorrador(Certificado $certificado, string $tokenBorrador): string
    {
        $rutaPdfOriginal = $certificado->ruta_pdf_original;

        if (! $rutaPdfOriginal) {
            throw new RuntimeException('La plantilla PDF no existe en el registro.');
        }

        $disco = Storage::disk((string) config('certificados.disk', 'public'));
        $directorioBorradores = (string) config('certificados.borradores_dir', 'certificados/borradores');

        if (! $disco->exists($rutaPdfOriginal)) {
            throw new RuntimeException('No se encontro la plantilla PDF en el almacenamiento.');
        }

        $rutaOriginal = $disco->path($rutaPdfOriginal);

        $pdf = new Fpdi;
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(0, 0, 0, true);
        $pdf->SetAutoPageBreak(false);
        $pdf->SetKeywords('CNSM-TOKEN:'.$tokenBorrador);

        $numeroPaginas = $pdf->setSourceFile($rutaOriginal);

        for ($pagina = 1; $pagina <= $numeroPaginas; $pagina++) {
            $paginaId = $pdf->importPage($pagina);
            $tamano = $this->obtenerTamanoPlantilla($pdf, $paginaId);

            $pdf->AddPage($tamano['orientation'], [$tamano['width'], $tamano['height']]);
            $pdf->useTemplate($paginaId);

            if ($pagina === 1) {
                $this->dibujarQr($pdf, $tamano, $certificado);
            }
        }

        $disco->makeDirectory($directorioBorradores);

        $rutaBorrador = $directorioBorradores.'/'.$certificado->id.'.pdf';
        $rutaSalida = $disco->path($rutaBorrador);

        $pdf->Output($rutaSalida, 'F');

        return $rutaBorrador;
    }

    /**
     * @param  array{width: float, height: float, orientation: string}  $tamano
     */
    private function dibujarQr(Fpdi $pdf, array $tamano, Certificado $certificado): void
    {
        $url = route('certificados.verificar', $certificado);

        // Posición actual: Esquina Superior Izquierda
        $xBase = self::QR_MARGEN_X;
        $yBase = self::QR_MARGEN_Y;

        $this->imprimirCodigoQr($pdf, $url, $xBase, $yBase);
        $this->imprimirTextosAdicionales($pdf, $certificado, $xBase, $yBase);
    }

    private function imprimirCodigoQr(Fpdi $pdf, string $url, float $x, float $y): void
    {
        $estiloQr = [
            'border' => 0,
            'padding' => 5,
            'fgcolor' => [0, 0, 0],
            'bgcolor' => [255, 255, 255],
        ];

        $pdf->write2DBarcode($url, 'QRCODE,H', $x, $y, self::QR_LADO, self::QR_LADO, $estiloQr, 'N');
    }

    private function imprimirTextosAdicionales(Fpdi $pdf, Certificado $certificado, float $xQr, float $yQr): void
    {
        $pdf->SetTextColor(0);

        // Calcular la posición Y inicial para los textos (debajo del QR + espacio)
        $yTexto = $yQr + self::QR_LADO + self::TEXTO_GAP;

        // Calcular retroceso en X para centrar el bloque de texto sobre el QR
        $xTexto = $xQr - ((self::QR_ANCHO_BLOQUE - self::QR_LADO) / 2);

        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY($xTexto, $yTexto);
        $pdf->Cell(self::QR_ANCHO_BLOQUE, 4, 'Emitido el: '.$certificado->fecha_emision_formateada, 0, 1, 'C');

        $pdf->SetXY($xTexto, $yTexto + 4);
        $pdf->Cell(self::QR_ANCHO_BLOQUE, 4, 'Código de verificación:', 0, 1, 'C');

        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->SetXY($xTexto, $yTexto + self::QR_ALTO_TEXTO);
        $pdf->Cell(self::QR_ANCHO_BLOQUE, 4, $certificado->codigo_certificado, 0, 1, 'C');
    }
}
