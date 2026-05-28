<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\EstadoCertificado;
use App\Models\Certificado;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DescargarCertificadoController extends Controller
{
    public function __invoke(Certificado $certificado): StreamedResponse
    {
        $esValido = $certificado->estado === EstadoCertificado::Firmado;
        $ruta = $esValido ? $certificado->ruta_pdf_firmado : $certificado->ruta_pdf_borrador;

        if (! $ruta) {
            abort(404);
        }

        $disco = Storage::disk((string) config('certificados.disk', 'public'));

        if (! $disco->exists($ruta)) {
            abort(404);
        }

        $prefijo = $esValido ? 'firmado' : 'borrador';
        $nombre = "certificado-{$prefijo}-{$certificado->id}.pdf";

        return $disco->download($ruta, $nombre);
    }
}
