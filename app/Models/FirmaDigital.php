<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

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

    public function getFechaFirmaAttribute($value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        $original = $this->metadatos_completos['firma']['fecha_firma'] ?? null;
        if (is_string($original) && $original !== '') {
            return Carbon::parse($original)->setTimezone((string) config('app.timezone'));
        }

        return Carbon::parse($value)->setTimezone((string) config('app.timezone'));
    }

    public function setFechaFirmaAttribute($value): void
    {
        $this->attributes['fecha_firma'] = $value
            ? Carbon::parse($value)->setTimezone((string) config('app.timezone'))->format('Y-m-d H:i:s')
            : null;
    }
}
