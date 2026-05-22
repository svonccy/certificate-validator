<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EstadoCertificado;
use Database\Factories\CertificadoFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Certificado extends Model
{
    /** @use HasFactory<CertificadoFactory> */
    use HasFactory, HasUlids;

    protected static function booted(): void
    {
        static::creating(function (Certificado $certificado) {
            if (empty($certificado->fecha_emision)) {
                $certificado->fecha_emision = now();
            }
        });
    }

    protected $fillable = [
        'titular_id',
        'codigo_certificado',
        'estado',
        'fecha_emision',
        'ruta_pdf_original',
        'ruta_pdf_borrador',
        'datos_qr',
        'qr_pagina',
        'token_borrador',
        'ruta_pdf_firmado',
    ];

    /**
     * @var array<string, string>
     */
    protected $attributes = [
        'estado' => 'PENDIENTE',
        'qr_pagina' => 1,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_emision' => 'datetime',
            'estado' => EstadoCertificado::class,
            'datos_qr' => 'array',
            'qr_pagina' => 'integer',
        ];
    }

    /**
     * Accesador para la fecha de emisión formateada (d/m/Y).
     * Usado por el generador de PDF.
     */
    protected function fechaEmisionFormateada(): Attribute
    {
        return Attribute::make(
            get: fn (): string => ($this->fecha_emision ?? now())->format('d/m/Y'),
        );
    }

    /**
     * Un certificado pertenece a un único titular (la persona física).
     */
    public function titular(): BelongsTo
    {
        return $this->belongsTo(Titular::class);
    }

    /**
     * Un certificado tiene una validación de firma digital.
     * Al inicio estará vacío, pero se llenará en la "Pasada 2" de tu diagrama.
     */
    public function firmaDigital(): HasOne
    {
        return $this->hasOne(FirmaDigital::class);
    }
}
