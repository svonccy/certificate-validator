<?php

declare(strict_types=1);

namespace App\Services\Certificados;

use App\DTO\DatosQr;
use App\DTO\PosicionQr;
use App\Enums\PresetQr;
use App\Models\Certificado;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use setasign\Fpdi\Tcpdf\Fpdi;

final class GeneradorPdfQr
{
    private const TEXTO_GAP = 1.0;

    private const TEXTO_LINEA_ALTO = 4.0;

    private const TEXTO_LINEAS = 3;

    private const TEXTO_ALTO_TOTAL = self::TEXTO_LINEA_ALTO * self::TEXTO_LINEAS;

    public function __construct(private readonly CalculadorPosicionQr $calculadorPosicion) {}

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
        $datosQr = DatosQr::desdeRegistro($certificado, $this->obtenerDefaultsQr());
        $paginaObjetivo = $this->ajustarPagina($datosQr->pagina, $numeroPaginas);

        for ($pagina = 1; $pagina <= $numeroPaginas; $pagina++) {
            $paginaId = $pdf->importPage($pagina);
            $tamano = $this->obtenerTamanoPlantilla($pdf, $paginaId);

            $pdf->AddPage($tamano['orientation'], [$tamano['width'], $tamano['height']]);
            $pdf->useTemplate($paginaId);

            if ($pagina === $paginaObjetivo) {
                $posicion = $this->calculadorPosicion->calcular(
                    $datosQr,
                    $tamano,
                    self::TEXTO_GAP,
                    self::TEXTO_ALTO_TOTAL,
                );
                $this->dibujarQr($pdf, $certificado, $posicion);
            }
        }

        $disco->makeDirectory($directorioBorradores);

        $rutaBorrador = $directorioBorradores.'/'.$certificado->id.'.pdf';
        $rutaSalida = $disco->path($rutaBorrador);

        $pdf->Output($rutaSalida, 'F');

        return $rutaBorrador;
    }

    private function dibujarQr(Fpdi $pdf, Certificado $certificado, PosicionQr $posicion): void
    {
        $url = route('certificados.verificar', $certificado);

        $this->imprimirCodigoQr($pdf, $url, $posicion->x, $posicion->y, $posicion->lado);
        $this->imprimirTextosAdicionales(
            $pdf,
            $certificado,
            $posicion->x,
            $posicion->y,
            $posicion->lado,
            $posicion->anchoBloque,
        );
    }

    private function imprimirCodigoQr(Fpdi $pdf, string $url, float $x, float $y, float $lado): void
    {
        $estiloQr = [
            'border' => 0,
            'padding' => 5,
            'fgcolor' => [0, 0, 0],
            'bgcolor' => [255, 255, 255],
        ];

        $pdf->write2DBarcode($url, 'QRCODE,H', $x, $y, $lado, $lado, $estiloQr, 'N');
    }

    private function imprimirTextosAdicionales(
        Fpdi $pdf,
        Certificado $certificado,
        float $xQr,
        float $yQr,
        float $lado,
        float $anchoBloque,
    ): void {
        $pdf->SetTextColor(0);

        // Calcular la posición Y inicial para los textos (debajo del QR + espacio)
        $yTexto = $yQr + $lado + self::TEXTO_GAP;

        // Calcular retroceso en X para centrar el bloque de texto sobre el QR
        $xTexto = $xQr - (($anchoBloque - $lado) / 2);

        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY($xTexto, $yTexto);
        $pdf->Cell($anchoBloque, self::TEXTO_LINEA_ALTO, 'Emitido el: '.$certificado->fecha_emision_formateada, 0, 1, 'C');

        // $pdf->SetXY($xTexto, $yTexto + self::TEXTO_LINEA_ALTO);
        // $pdf->Cell($anchoBloque, self::TEXTO_LINEA_ALTO, 'Código de verificación:', 0, 1, 'C');

        // $pdf->SetFont('helvetica', 'B', 7);
        // $pdf->SetXY($xTexto, $yTexto + (self::TEXTO_LINEA_ALTO * 2));
        // $pdf->Cell($anchoBloque, self::TEXTO_LINEA_ALTO, $certificado->codigo_certificado, 0, 1, 'C');
    }

    /**
     * @return array<string, mixed>
     */
    private function obtenerDefaultsQr(): array
    {
        $defaults = config('certificados.defaults');

        return is_array($defaults) ? $defaults : [
            'preset' => PresetQr::SuperiorIzquierda->value,
            'lado' => 30.0,
            'margen_x' => 5.0,
            'margen_y' => 5.0,
            'ancho_bloque_factor' => 1.2,
            'pagina' => 1,
        ];
    }

    private function ajustarPagina(int $pagina, int $totalPaginas): int
    {
        if ($totalPaginas < 1) {
            return 1;
        }

        if ($pagina < 1) {
            return 1;
        }

        if ($pagina > $totalPaginas) {
            return $totalPaginas;
        }

        return $pagina;
    }
}
