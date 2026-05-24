<?php

declare(strict_types=1);

namespace App\Services\Certificados;

use App\DTO\DatosQr;
use App\DTO\PosicionQr;
use App\Enums\PresetQr;
use App\Models\Certificado;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;

final class GeneradorPdfQr
{
    private const TEXTO_GAP = 1.0;

    private const TEXTO_LINEA_ALTO = 4.0;

    private const TEXTO_LINEAS = 1;

    private const TEXTO_ALTO_TOTAL = self::TEXTO_LINEA_ALTO * self::TEXTO_LINEAS;

    public function __construct(
        private readonly CalculadorPosicionQr $calculadorPosicion,
        private readonly EditorPdfContract $pdfEditor,
        private readonly NormalizadorPdfContract $pdfNormalizer
    ) {}

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

        $this->pdfEditor->establecerKeywords('CNSM-TOKEN:'.$tokenBorrador);

        try {
            $numeroPaginas = $this->pdfEditor->cargarOrigen($rutaOriginal);
        } catch (CrossReferenceException $exception) {
            $this->pdfNormalizer->normalizar($rutaOriginal);
            $numeroPaginas = $this->pdfEditor->cargarOrigen($rutaOriginal);
        }

        $datosQr = DatosQr::desdeRegistro($certificado, $this->obtenerDefaultsQr());
        $paginaObjetivo = $this->ajustarPagina($datosQr->pagina, $numeroPaginas);

        for ($pagina = 1; $pagina <= $numeroPaginas; $pagina++) {
            $tamano = $this->pdfEditor->clonarPagina($pagina);

            if ($pagina === $paginaObjetivo) {
                $posicion = $this->calculadorPosicion->calcular(
                    $datosQr,
                    $tamano,
                    self::TEXTO_GAP,
                    self::TEXTO_ALTO_TOTAL,
                );
                $this->dibujarQr($certificado, $posicion);
            }
        }

        $disco->makeDirectory($directorioBorradores);

        $rutaBorrador = $directorioBorradores.'/'.$certificado->id.'.pdf';
        $rutaSalida = $disco->path($rutaBorrador);

        $this->pdfEditor->guardarEn($rutaSalida);

        return $rutaBorrador;
    }

    private function dibujarQr(Certificado $certificado, PosicionQr $posicion): void
    {
        $url = route('certificados.verificar', $certificado);

        $this->pdfEditor->dibujarQr($url, $posicion->xQr, $posicion->yQr, $posicion->lado);
        $this->imprimirTextosAdicionales(
            $certificado,
            $posicion->xQr,
            $posicion->yQr,
            $posicion->lado,
            $posicion->anchoBloque,
            $posicion->textoArriba,
        );
    }

    private function imprimirTextosAdicionales(
        Certificado $certificado,
        float $xQr,
        float $yQr,
        float $lado,
        float $anchoBloque,
        bool $textoArriba,
    ): void {
        // Calcular la posición Y inicial para los textos según la fila.
        $yTexto = $textoArriba
            ? $yQr - self::TEXTO_GAP - self::TEXTO_ALTO_TOTAL
            : $yQr + $lado + self::TEXTO_GAP;

        // Calcular retroceso en X para centrar el bloque de texto sobre el QR
        $xTexto = $xQr - (($anchoBloque - $lado) / 2);

        $this->pdfEditor->escribirTextoCentrado(
            'Emitido el: '.$certificado->fecha_emision_formateada,
            $xTexto,
            $yTexto,
            $anchoBloque,
            self::TEXTO_LINEA_ALTO
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function obtenerDefaultsQr(): array
    {
        $defaults = config('certificados.defaults');

        return is_array($defaults) ? $defaults : [
            'preset' => PresetQr::Superior1->value,
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
