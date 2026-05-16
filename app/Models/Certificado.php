<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CertificadoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'dni_titular',
    'nombre_titular',
    'codigo_certificado',
    'estado',
    'ruta_pdf_original',
    'ruta_pdf_borrador',
    'token_borrador',
    'ruta_pdf_firmado',
    'firma_valida',
    'firma_fecha',
    'firma_serial',
    'firma_algoritmo',
    'hash_pdf_firmado',
    'firma_notario_nombre',
    'firma_notario_documento',
    'metadatos_firma',
    'validado_en',
    'fecha_emision',
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

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'firma_valida' => 'boolean',
            'firma_fecha' => 'datetime',
            'validado_en' => 'datetime',
            'metadatos_firma' => 'array',
            'fecha_emision' => 'datetime', // Se añade el casteo nativo a Carbon para este campo
        ];
    }

    /**
     * Accesador moderno para la fecha de emisión formateada.
     * Esto expone la propiedad dinámica `$certificado->fecha_emision_formateada`.
     */
    protected function fechaEmisionFormateada(): Attribute
    {
        return Attribute::make(
            get: fn (): string => ($this->fecha_emision ?? now())->format('d/m/Y'),
        );
    }
}
