<?php

declare(strict_types=1);

use App\Models\Certificado;
use App\Models\Titular;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

pest()->use(RefreshDatabase::class);

test('it keeps files on soft delete, but deletes them on force delete', function (): void {
    $diskName = (string) config('certificados.disk', 'public');
    Storage::fake($diskName);
    $disk = Storage::disk($diskName);

    // Put dummy files
    $originalPath = 'certificados/originales/template.pdf';
    $borradorPath = 'certificados/borradores/draft.pdf';
    $firmadoPath = 'certificados/firmados/signed.pdf';

    $disk->put($originalPath, 'original content');
    $disk->put($borradorPath, 'borrador content');
    $disk->put($firmadoPath, 'firmado content');

    expect($disk->exists($originalPath))->toBeTrue();
    expect($disk->exists($borradorPath))->toBeTrue();
    expect($disk->exists($firmadoPath))->toBeTrue();

    $titular = Titular::query()->create([
        'dni' => '12345678',
        'nombre_completo' => 'Pest Tester',
    ]);

    $certificado = Certificado::query()->create([
        'titular_id' => $titular->getKey(),
        'codigo_certificado' => 'TEST-0001',
        'ruta_pdf_original' => $originalPath,
        'ruta_pdf_borrador' => $borradorPath,
        'ruta_pdf_firmado' => $firmadoPath,
    ]);

    // Soft delete the certificate
    $certificado->delete();

    // Verify files still exist on soft delete
    expect($disk->exists($originalPath))->toBeTrue();
    expect($disk->exists($borradorPath))->toBeTrue();
    expect($disk->exists($firmadoPath))->toBeTrue();

    // Force delete the certificate
    $certificado->forceDelete();

    // Verify files are physically deleted on force delete
    expect($disk->exists($originalPath))->toBeFalse();
    expect($disk->exists($borradorPath))->toBeFalse();
    expect($disk->exists($firmadoPath))->toBeFalse();
});

test('it deletes the old file when a file path attribute is updated', function (): void {
    $diskName = (string) config('certificados.disk', 'public');
    Storage::fake($diskName);
    $disk = Storage::disk($diskName);

    $oldOriginalPath = 'certificados/originales/old_template.pdf';
    $newOriginalPath = 'certificados/originales/new_template.pdf';

    $disk->put($oldOriginalPath, 'old content');
    $disk->put($newOriginalPath, 'new content');

    $titular = Titular::query()->create([
        'dni' => '87654321',
        'nombre_completo' => 'Pest Tester 2',
    ]);

    $certificado = Certificado::query()->create([
        'titular_id' => $titular->getKey(),
        'codigo_certificado' => 'TEST-0002',
        'ruta_pdf_original' => $oldOriginalPath,
    ]);

    // Update the original PDF path
    $certificado->update([
        'ruta_pdf_original' => $newOriginalPath,
    ]);

    // Verify the old file is deleted, and the new file remains
    expect($disk->exists($oldOriginalPath))->toBeFalse();
    expect($disk->exists($newOriginalPath))->toBeTrue();
});
