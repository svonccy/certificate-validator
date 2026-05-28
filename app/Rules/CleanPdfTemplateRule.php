<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class CleanPdfTemplateRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
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
                } catch (\Throwable $e) {
                    //
                }
            }
        }
    }
}
