<?php

declare(strict_types=1);

namespace App\Services\Certificados;

use App\Models\Certificado;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use setasign\Fpdi\Tcpdf\Fpdi;

final class GeneradorPdfQr
{
    private const DISCO = 'local';

    private const DIRECTORIO_BORRADORES = 'certificados/borradores';

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

        $disco = Storage::disk(self::DISCO);

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

        $disco->makeDirectory(self::DIRECTORIO_BORRADORES);

        $rutaBorrador = self::DIRECTORIO_BORRADORES.'/'.$certificado->id.'.pdf';
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

        $margen = 10.0;
        $lado = 28.0;
        $x = $tamano['width'] - $lado - $margen;
        $y = $tamano['height'] - $lado - $margen;

        $pdf->write2DBarcode(
            $url,
            'QRCODE,H',
            $x,
            $y,
            $lado,
            $lado,
            [
                'border' => 0,
                'padding' => 0,
                'fgcolor' => [0, 0, 0],
                'bgcolor' => false,
            ],
            'N'
        );
    }
}
