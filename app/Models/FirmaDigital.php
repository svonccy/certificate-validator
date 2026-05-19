<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FirmaDigital extends Model
{
    protected $table = 'firmas_digitales';

    protected $fillable = [
        'certificado_id', 'es_valida', 'fecha_firma', 'serial',
        'algoritmo', 'hash_documento', 'notario_nombre',
        'notario_documento', 'metadatos_completos',
    ];

    protected $casts = [
        'es_valida' => 'boolean',
        'fecha_firma' => 'datetime',
        'metadatos_completos' => 'array',
    ];

    public function certificado(): BelongsTo
    {
        return $this->belongsTo(Certificado::class);
    }
}
