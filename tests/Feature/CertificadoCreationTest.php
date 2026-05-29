<?php

declare(strict_types=1);

use App\Enums\EstadoCertificado;
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

    // Check default state is PENDIENTE_QR
    expect($certificado->estado->value)->toBe('PENDIENTE_QR');

    // Check fecha_firma is null on creation
    expect($certificado->fecha_firma)->toBeNull();
});

test('certificado soft deletes is enabled and does logical delete', function (): void {
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

    // The count should be 0 because soft delete is active and filters out trashed records by default
    expect(Certificado::query()->count())->toBe(0);
    // But the record should still exist in the database with soft delete
    expect(Certificado::withTrashed()->count())->toBe(1);
    expect($certificado->trashed())->toBeTrue();
});

test('certificado created without pdf starts as PDF_NO_ENCONTRADO', function (): void {
    $titular = Titular::query()->create([
        'dni' => '87654321',
        'nombre_completo' => 'John Doe',
    ]);

    $certificado = Certificado::query()->create([
        'titular_id' => $titular->getKey(),
        'codigo_certificado' => 'CERT-99998',
    ]);

    expect($certificado->estado->value)->toBe('PDF_NO_ENCONTRADO');
});

test('certificado transition logic on update', function (): void {
    $titular = Titular::query()->create([
        'dni' => '87654321',
        'nombre_completo' => 'John Doe',
    ]);

    // Starts without PDF
    $certificado = Certificado::query()->create([
        'titular_id' => $titular->getKey(),
        'codigo_certificado' => 'CERT-99997',
    ]);

    expect($certificado->estado->value)->toBe('PDF_NO_ENCONTRADO');

    // Add PDF -> should transition to PENDIENTE_QR
    $certificado->update([
        'ruta_pdf_original' => 'certificados/originales/john.pdf',
    ]);

    expect($certificado->estado->value)->toBe('PENDIENTE_QR');

    // Remove PDF -> should transition back to PDF_NO_ENCONTRADO
    $certificado->update([
        'ruta_pdf_original' => null,
    ]);

    expect($certificado->estado->value)->toBe('PDF_NO_ENCONTRADO');
});

test('certificado in advanced states does not transition automatically', function (): void {
    $titular = Titular::query()->create([
        'dni' => '87654321',
        'nombre_completo' => 'John Doe',
    ]);

    $certificado = Certificado::query()->create([
        'titular_id' => $titular->getKey(),
        'codigo_certificado' => 'CERT-99996',
        'ruta_pdf_original' => 'certificados/originales/john.pdf',
    ]);

    expect($certificado->estado->value)->toBe('PENDIENTE_QR');

    // Manually force to FIRMADO (advanced state)
    $certificado->estado = EstadoCertificado::Firmado;
    $certificado->save();

    expect($certificado->estado->value)->toBe('FIRMADO');

    // Update with empty/null original pdf path -> should NOT change state back to PDF_NO_ENCONTRADO because state is FIRMADO
    $certificado->update([
        'ruta_pdf_original' => null,
    ]);

    expect($certificado->estado->value)->toBe('FIRMADO');
});
