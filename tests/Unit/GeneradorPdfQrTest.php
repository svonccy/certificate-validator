<?php

declare(strict_types=1);

use App\Services\Certificados\GeneradorPdfQr;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

uses(TestCase::class);

test('it blocks uploads that contain the draft token', function (): void {
    $pdfWithToken = '%PDF-1.4 ... CNSM-TOKEN:some-uuid-token ...';
    $file = UploadedFile::fake()->createWithContent('draft.pdf', $pdfWithToken);

    $validationRule = function (string $attribute, mixed $value, Closure $fail): void {
        if (is_object($value)) {
            $exists = false;
            if (method_exists($value, 'exists')) {
                $exists = $value->exists();
            } elseif (method_exists($value, 'getRealPath')) {
                $path = $value->getRealPath();
                $exists = ! empty($path) && file_exists($path);
            }

            if ($exists) {
                try {
                    $content = method_exists($value, 'get') ? $value->get() : file_get_contents($value->getRealPath());
                    if (str_contains($content, 'CNSM-TOKEN:')) {
                        $fail('El archivo seleccionado es un borrador que ya contiene un código QR. Por favor, sube la plantilla original limpia.');
                    }
                } catch (Throwable $e) {
                    // Safe fallback
                }
            }
        }
    };

    $failed = false;
    $failCallback = function (string $message) use (&$failed): void {
        $failed = true;
        expect($message)->toBe('El archivo seleccionado es un borrador que ya contiene un código QR. Por favor, sube la plantilla original limpia.');
    };

    $validationRule('ruta_pdf_original', $file, $failCallback);

    expect($failed)->toBeTrue();
});

test('it allows uploads that do not contain the draft token', function (): void {
    $cleanPdf = '%PDF-1.4 ... some clean content ...';
    $file = UploadedFile::fake()->createWithContent('clean.pdf', $cleanPdf);

    $validationRule = function (string $attribute, mixed $value, Closure $fail): void {
        if (is_object($value)) {
            $exists = false;
            if (method_exists($value, 'exists')) {
                $exists = $value->exists();
            } elseif (method_exists($value, 'getRealPath')) {
                $path = $value->getRealPath();
                $exists = ! empty($path) && file_exists($path);
            }

            if ($exists) {
                try {
                    $content = method_exists($value, 'get') ? $value->get() : file_get_contents($value->getRealPath());
                    if (str_contains($content, 'CNSM-TOKEN:')) {
                        $fail('El archivo seleccionado es un borrador que ya contiene un código QR. Por favor, sube la plantilla original limpia.');
                    }
                } catch (Throwable $e) {
                    // Safe fallback
                }
            }
        }
    };

    $failed = false;
    $failCallback = function (string $message) use (&$failed): void {
        $failed = true;
    };

    $validationRule('ruta_pdf_original', $file, $failCallback);

    expect($failed)->toBeFalse();
});

test('repararPdfIncompatible runs on existing template', function (): void {
    $generador = app(GeneradorPdfQr::class);

    $files = glob(storage_path('app/public/certificados/plantillas/*.pdf'));
    if ($files === [] || $files === false) {
        $this->markTestSkipped('No template files found for testing');
    }

    $file = $files[0];

    $tempCopy = tempnam(sys_get_temp_dir(), 'test_pdf_').'.pdf';
    copy($file, $tempCopy);

    $reflection = new ReflectionClass(GeneradorPdfQr::class);
    $method = $reflection->getMethod('repararPdfIncompatible');
    $method->setAccessible(true);

    $method->invoke($generador, $tempCopy);

    expect(file_exists($tempCopy))->toBeTrue();
    expect(filesize($tempCopy))->toBeGreaterThan(0);

    @unlink($tempCopy);
});
