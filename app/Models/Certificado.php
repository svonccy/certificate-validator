<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CertificadoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'dni_titular',
    'nombre_titular',
    'tipo_certificado',
    'estado',
    'ruta_pdf_original',
    'ruta_pdf_firmado',
])]
class Certificado extends Model
{
    /** @use HasFactory<CertificadoFactory> */
    use HasFactory, HasUuids;

    /**
     * @var array<string, string>
     */
    protected $attributes = [
        'estado' => 'PENDIENTE',
    ];
}
