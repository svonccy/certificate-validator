<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FirmaDigital extends Model
{
    protected $table = 'firmas_digitales';

    protected $fillable = [
        'certificado_id', 'es_valida', 'fecha_firma', 'serial',
        'algoritmo', 'hash_documento', 'notario_nombre',
        'notario_documento', 'metadatos_completos'
    ];

    protected $casts = [
        'es_valida' => 'boolean',
        'fecha_firma' => 'datetime',
        'metadatos_completos' => 'array',
    ];

    public function certificado()
    {
        return $this->belongsTo(Certificado::class);
    }
}
