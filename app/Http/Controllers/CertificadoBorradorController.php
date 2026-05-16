<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Certificado;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificadoBorradorController extends Controller
{
    public function __invoke(Certificado $certificado): StreamedResponse
    {
        $rutaBorrador = $certificado->ruta_pdf_borrador;

        if (! $rutaBorrador) {
            abort(404);
        }

        $disco = Storage::disk('public');

        if (! $disco->exists($rutaBorrador)) {
            abort(404);
        }

        $nombre = 'certificado-'.$certificado->id.'.pdf';

        return $disco->download($rutaBorrador, $nombre);
    }
}
