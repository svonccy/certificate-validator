<?php

declare(strict_types=1);

use App\Models\Certificado;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

test('the public verification page shows certificate details', function (): void {
    $certificado = Certificado::query()->forceCreate([
        'dni_titular' => '12345678',
        'nombre_titular' => 'Joy Tivi',
        'codigo_certificado' => 'CERT-00001',
        'estado' => 'VALIDO',
        'ruta_pdf_original' => 'certificados/originales/demo.pdf',
        'ruta_pdf_borrador' => 'certificados/borradores/demo.pdf',
        'token_borrador' => 'demo-token',
        'ruta_pdf_firmado' => 'certificados/firmados/demo.pdf',
        'firma_valida' => true,
        'firma_fecha' => now(),
        'firma_serial' => '0x1234',
        'firma_algoritmo' => 'sha256',
        'hash_pdf_firmado' => 'sha256:'.str_repeat('a', 64),
        'firma_notario_nombre' => 'EDWIN DANTE BARRIOS FALCON',
        'firma_notario_documento' => '20904524',
        'metadatos_firma' => [
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
        'validado_en' => now(),
    ]);

    $response = get(route('certificados.verificar', $certificado));

    $response->assertOk();
    $response->assertSee('Certificado verificado');
    $response->assertSee('Válido');
    $response->assertSee('Joy Tivi');
    $response->assertSee('Validacion de prueba');
});
