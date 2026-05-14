<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CertificadoConfianzaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'nombre',
    'ruta_certificado',
    'activo',
])]
class CertificadoConfianza extends Model
{
    /** @use HasFactory<CertificadoConfianzaFactory> */
    use HasFactory, HasUuids;

    protected $table = 'certificados_confianza';

    /**
     * @var array<string, bool>
     */
    protected $attributes = [
        'activo' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }
}
