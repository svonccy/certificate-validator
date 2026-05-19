<?php

declare(strict_types=1);

use App\Models\Certificado;
use App\Models\FirmaDigital;
use App\Models\Titular;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

test('the public verification page shows certificate details', function (): void {
    $titular = Titular::query()->create([
        'dni' => '12345678',
        'nombre_completo' => 'Joy Tivi',
    ]);

    $certificado = Certificado::query()->create([
        'titular_id' => $titular->getKey(),
        'codigo_certificado' => 'CERT-00001',
        'estado' => 'VALIDO',
        'ruta_pdf_original' => 'certificados/originales/demo.pdf',
        'ruta_pdf_borrador' => 'certificados/borradores/demo.pdf',
        'token_borrador' => 'demo-token',
        'ruta_pdf_firmado' => 'certificados/firmados/demo.pdf',
    ]);

    FirmaDigital::query()->create([
        'certificado_id' => $certificado->getKey(),
        'es_valida' => true,
        'fecha_firma' => now(),
        'serial' => '0x1234',
        'algoritmo' => 'sha256',
        'hash_documento' => 'sha256:'.str_repeat('a', 64),
        'notario_nombre' => 'EDWIN DANTE BARRIOS FALCON',
        'notario_documento' => '20904524',
        'metadatos_completos' => [
            'valido' => true,
            'motivo' => null,
            'firma' => [
                'algoritmo' => 'sha256',
                'cadena_confiable' => true,
                'integridad' => true,
            ],
            'firmante' => [
                'nombre' => 'EDWIN DANTE BARRIOS FALCON',
                'documento' => '20904524',
            ],
            'certificado' => [
                'issuer' => 'RENIEC',
            ],
            'detalle' => 'Validacion de prueba',
            'borrador_coincide' => true,
        ],
    ]);

    $response = get(route('certificados.verificar', $certificado));

    $response->assertOk();
    $response->assertSee('Certificado verificado');
    $response->assertSee('Válido');
    $response->assertSee('Joy Tivi');
    $response->assertSee('Validacion de prueba');
});
