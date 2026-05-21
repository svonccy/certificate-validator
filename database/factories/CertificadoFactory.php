<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EstadoCertificado;
use App\Models\Certificado;
use App\Models\Titular;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
            'titular_id' => function (): int {
                $titular = Titular::query()->create([
                    'dni' => fake()->unique()->numerify('########'),
                    'nombre_completo' => fake()->name(),
                ]);

                return (int) $titular->getKey();
            },
            'codigo_certificado' => fake()->unique()->bothify('CERT-#####'),
            'estado' => fake()->randomElement(EstadoCertificado::cases()),
            'fecha_emision' => now(),
            'ruta_pdf_original' => 'certificados/originales/'.Str::uuid().'.pdf',
            'ruta_pdf_borrador' => 'certificados/borradores/'.Str::uuid().'.pdf',
            'qr_pagina' => 1,
            'token_borrador' => (string) Str::uuid(),
            'ruta_pdf_firmado' => 'certificados/firmados/'.Str::uuid().'.pdf',
        ];
    }
}
