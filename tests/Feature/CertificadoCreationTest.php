<?php

declare(strict_types=1);

use App\Models\Certificado;
use App\Models\Titular;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

test('certificado set default values on creation', function (): void {
    $titular = Titular::query()->create([
        'dni' => '87654321',
        'nombre_completo' => 'John Doe',
    ]);

    $certificado = Certificado::query()->create([
        'titular_id' => $titular->getKey(),
        'codigo_certificado' => 'CERT-99999',
        'ruta_pdf_original' => 'certificados/originales/john.pdf',
        'ruta_pdf_borrador' => 'certificados/borradores/john.pdf',
        'token_borrador' => 'john-token',
        'ruta_pdf_firmado' => 'certificados/firmados/john.pdf',
    ]);

    // Check default state is PENDIENTE
    expect($certificado->estado->value)->toBe('PENDIENTE');

    // Check fecha_emision is populated automatically
    expect($certificado->fecha_emision)->not->toBeNull();
    expect($certificado->fecha_emision->isToday())->toBeTrue();
});

test('certificado soft deletes is removed and does physical delete', function (): void {
    $titular = Titular::query()->create([
        'dni' => '11223344',
        'nombre_completo' => 'Jane Smith',
    ]);

    $certificado = Certificado::query()->create([
        'titular_id' => $titular->getKey(),
        'codigo_certificado' => 'CERT-11223',
    ]);

    expect(Certificado::query()->count())->toBe(1);

    $certificado->delete();

    // The count should be 0 because soft delete is removed
    expect(Certificado::query()->count())->toBe(0);
});
