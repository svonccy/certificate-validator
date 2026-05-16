<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Titular extends Model
{
    protected $table = 'titulares';
    protected $fillable = ['dni', 'nombre_completo'];

    public function certificados()
    {
        return $this->hasMany(Certificado::class);
    }
}
