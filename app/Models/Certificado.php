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
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Certificado extends Model
{
    /** @use HasFactory<CertificadoFactory> */
    use HasFactory, HasUlids, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (Certificado $certificado) {
            if (empty($certificado->fecha_emision)) {
                $certificado->fecha_emision = now();
            }
        });

        static::saving(function (Certificado $certificado) {
            // Only auto-transition if the certificate is not yet in PENDIENTE_FIRMA, FIRMADO or RECHAZADO
            if (empty($certificado->estado) ||
                $certificado->estado === EstadoCertificado::PdfNoEncontrado ||
                $certificado->estado === EstadoCertificado::PendienteQr
            ) {
                if (empty($certificado->ruta_pdf_original)) {
                    $certificado->estado = EstadoCertificado::PdfNoEncontrado;
                } else {
                    $certificado->estado = EstadoCertificado::PendienteQr;
                }
            }
        });

        static::updating(function (Certificado $certificado) {
            $diskName = (string) config('certificados.disk', 'public');
            $disk = Storage::disk($diskName);

            foreach (['ruta_pdf_original', 'ruta_pdf_firmado'] as $field) {
                if ($certificado->isDirty($field)) {
                    $oldPath = $certificado->getOriginal($field);
                    if ($oldPath && $disk->exists($oldPath)) {
                        $disk->delete($oldPath);
                    }
                }
            }
        });

        static::forceDeleted(function (Certificado $certificado) {
            $diskName = (string) config('certificados.disk', 'public');
            $disk = Storage::disk($diskName);

            foreach (['ruta_pdf_original', 'ruta_pdf_borrador', 'ruta_pdf_firmado'] as $field) {
                $path = $certificado->getAttribute($field);
                if ($path && $disk->exists($path)) {
                    $disk->delete($path);
                }
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
        'estado' => 'PDF_NO_ENCONTRADO',
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
