<?php

declare(strict_types=1);

use App\Rules\CleanPdfTemplateRule;

test('it passes when the file does not contain cnsm-token', function (): void {
    $rule = new CleanPdfTemplateRule;

    $file = new class
    {
        public function exists(): bool
        {
            return true;
        }

        public function get(): string
        {
            return 'This is a clean PDF template with no token.';
        }
    };

    $failed = false;
    $rule->validate('ruta_pdf_original', $file, function (string $message) use (&$failed): void {
        $failed = true;
    });

    expect($failed)->toBeFalse();
});

test('it fails when the file contains cnsm-token', function (): void {
    $rule = new CleanPdfTemplateRule;

    $file = new class
    {
        public function exists(): bool
        {
            return true;
        }

        public function get(): string
        {
            return 'This file contains CNSM-TOKEN:12345 and is a draft.';
        }
    };

    $failed = false;
    $rule->validate('ruta_pdf_original', $file, function (string $message) use (&$failed): void {
        $failed = true;
        expect($message)->toBe('El archivo seleccionado es un borrador que ya contiene un código QR. Por favor, sube la plantilla original limpia.');
    });

    expect($failed)->toBeTrue();
});
