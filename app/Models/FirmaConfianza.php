<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\FirmaConfianzaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'nombre',
    'ruta_firma',
    'activo',
])]
class FirmaConfianza extends Model
{
    /** @use HasFactory<FirmaConfianzaFactory> */
    use HasFactory, HasUuids;

    protected $table = 'firmas_confianza';

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
