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

class Certificado extends Model
{
    /** @use HasFactory<CertificadoFactory> */
    use HasFactory, HasUlids, SoftDeletes;

    /**
     * Los atributos que se pueden asignar masivamente.
     * Fíjate cómo desaparecieron todas las columnas de la firma y el DNI.
     */
    protected $fillable = [
        'titular_id', // El enlace a la tabla titulares
        'codigo_certificado',
        'estado',
        'fecha_emision',
        'ruta_pdf_original',
        'ruta_pdf_borrador',
        'token_borrador',
        'ruta_pdf_firmado',
    ];

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
            'fecha_emision' => 'datetime',
            'estado' => EstadoCertificado::class,
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (self $certificado): void {
            if ($certificado->isForceDeleting()) {
                $certificado->firmaDigital()->withTrashed()->forceDelete();

                return;
            }

            $certificado->firmaDigital()->delete();
        });
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
