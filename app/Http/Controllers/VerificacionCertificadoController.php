<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\EstadoCertificado;
use App\Models\Certificado;
use Illuminate\View\View;

class VerificacionCertificadoController extends Controller
{
    public function __invoke(Certificado $certificado): View
    {
        // 1. Cargar las relaciones para evitar el problema de N+1 consultas en la vista
        $certificado->load(['titular', 'firmaDigital']);

        // 2. Extraer la firma y sus metadatos desde la nueva tabla (si existe)
        $firma = $certificado->firmaDigital;
        $metadatosFirma = $firma ? $firma->metadatos_completos : [];
        $estado = $certificado->estado;

        return view('verificacion', [
            'certificado' => $certificado,
            'titular' => $certificado->titular,
            'firmaDigital' => $firma,
            'estadoTexto' => $estado instanceof EstadoCertificado ? $estado->getLabel() : 'Pendiente',
            'estadoClase' => $estado instanceof EstadoCertificado ? $estado->getColor() : 'warning',

            // Lectura de la llave JSON que ahora viene de FirmaDigital
            'firma' => $metadatosFirma['firma'] ?? [],
            'firmante' => $metadatosFirma['firmante'] ?? [],
            'certificadoFirma' => $metadatosFirma['certificado'] ?? [],
            'detalleFirma' => (string) ($metadatosFirma['detalle'] ?? ''),
            'borradorCoincide' => (bool) ($metadatosFirma['borrador_coincide'] ?? true),
        ]);
    }
}
