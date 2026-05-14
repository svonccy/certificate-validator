<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CertificadoConfianza;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CertificadoConfianza>
 */
class CertificadoConfianzaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->company(),
            'ruta_certificado' => 'certificados/trust-roots/'.fake()->lexify('cert-??????').'.cer',
            'activo' => true,
        ];
    }
}
