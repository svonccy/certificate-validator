<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Certificado;
use Illuminate\View\View;

class VerificacionCertificadoController extends Controller
{
    public function __invoke(Certificado $certificado): View
    {
        return view('verificacion', [
            'certificado' => $certificado,
        ]);
    }
}
