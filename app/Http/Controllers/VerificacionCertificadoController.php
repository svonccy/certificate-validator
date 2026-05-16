<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Certificado;
use Illuminate\View\View;

class VerificacionCertificadoController extends Controller
{
    public function __invoke(Certificado $certificado): View
    {
        $metadatosFirma = $certificado->metadatos_firma ?? [];

        return view('verificacion', [
            'certificado' => $certificado,
            'estadoTexto' => match ($certificado->estado) {
                'VALIDO' => 'Válido',
                'RECHAZADO' => 'Rechazado',
                default => 'Pendiente',
            },
            'estadoClase' => match ($certificado->estado) {
                'VALIDO' => 'success',
                'RECHAZADO' => 'danger',
                default => 'warning',
            },
            'firma' => $metadatosFirma['firma'] ?? [],
            'firmante' => $metadatosFirma['firmante'] ?? [],
            'certificadoFirma' => $metadatosFirma['certificado'] ?? [],
            'detalleFirma' => (string) ($metadatosFirma['detalle'] ?? ''),
            'borradorCoincide' => (bool) ($metadatosFirma['borrador_coincide'] ?? true),
        ]);
    }
}
