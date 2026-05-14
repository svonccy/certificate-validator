<?php

declare(strict_types=1);

namespace App\Services\Certificados;

use App\Models\CertificadoConfianza;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;

final class ValidadorFirmaPdf
{
    private const DISCO = 'local';

    private const TIMEOUT = 60;

    /**
     * @return array<string, mixed>
     */
    public function validar(string $rutaPdfFirmado, ?string $tokenBorrador = null): array
    {
        $disco = Storage::disk(self::DISCO);

        if (! $disco->exists($rutaPdfFirmado)) {
            throw new RuntimeException('No se encontro el PDF firmado en el almacenamiento.');
        }

        $rutaScript = base_path('scripts/validar_firma.py');

        if (! is_file($rutaScript)) {
            throw new RuntimeException('No se encontro el script de validacion.');
        }

        $rutaAbsoluta = $disco->path($rutaPdfFirmado);

        $trustRoots = $this->obtenerTrustRoots();
        $env = $this->obtenerEntornoProceso([
            'CNSM_TRUST_ROOTS' => $trustRoots !== [] ? implode(',', $trustRoots) : '',
        ]);

        $comando = ['python3', $rutaScript, $rutaAbsoluta];

        if ($tokenBorrador) {
            $comando[] = '--token';
            $comando[] = $tokenBorrador;
        }

        $proceso = new Process($comando, null, $env);
        $proceso->setTimeout(self::TIMEOUT);
        $proceso->run();

        $salida = trim($proceso->getOutput());

        if ($salida === '') {
            throw new RuntimeException('El validador no devolvio respuesta.');
        }

        $resultado = json_decode($salida, true);

        if (! is_array($resultado)) {
            throw new RuntimeException('Respuesta invalida del validador.');
        }

        if (! $proceso->isSuccessful()) {
            $motivo = $resultado['motivo'] ?? null;
            $detalle = $motivo ?: trim($proceso->getErrorOutput());

            throw new RuntimeException($detalle !== '' ? $detalle : 'La validacion de la firma fallo.');
        }

        return $resultado;
    }

    /**
     * @return array<int, string>
     */
    private function obtenerTrustRoots(): array
    {
        $disco = Storage::disk(self::DISCO);

        return CertificadoConfianza::query()
            ->where('activo', true)
            ->pluck('ruta_certificado')
            ->filter()
            ->map(fn (string $ruta): string => $disco->path($ruta))
            ->filter(fn (string $ruta): bool => is_file($ruta))
            ->values()
            ->all();
    }

    /**
     * @param  array<string, string>  $extra
     * @return array<string, string>
     */
    private function obtenerEntornoProceso(array $extra): array
    {
        $base = array_merge($_SERVER, $_ENV);

        $base = array_filter(
            $base,
            static fn ($value, $key): bool => is_string($key) && (is_scalar($value) || $value === null),
            ARRAY_FILTER_USE_BOTH
        );

        $base = array_map(static fn ($value): string => (string) $value, $base);

        return array_merge($base, $extra);
    }
}
