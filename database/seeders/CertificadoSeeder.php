<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Certificado;
use Illuminate\Database\Seeder;

class CertificadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Certificado::factory()->count(5)->create();
    }
}
