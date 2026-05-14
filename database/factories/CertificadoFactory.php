<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Certificado;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Certificado>
 */
class CertificadoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dni_titular' => fake()->numerify('########'),
            'nombre_titular' => fake()->name(),
            'tipo_certificado' => fake()->randomElement([
                'Constancia',
                'Copia certificada',
                'Certificado simple',
            ]),
            'estado' => fake()->randomElement(['PENDIENTE', 'VALIDO']),
            'ruta_pdf_original' => null,
            'ruta_pdf_borrador' => null,
            'token_borrador' => null,
            'ruta_pdf_firmado' => null,
            'firma_valida' => false,
            'firma_fecha' => null,
            'firma_serial' => null,
            'firma_algoritmo' => null,
            'hash_pdf_firmado' => null,
            'firma_notario_nombre' => null,
            'firma_notario_documento' => null,
            'metadatos_firma' => null,
            'validado_en' => null,
        ];
    }
}
