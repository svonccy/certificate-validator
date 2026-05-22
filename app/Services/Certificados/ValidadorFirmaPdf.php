<?php

declare(strict_types=1);

namespace App\Services\Certificados;

use App\Models\FirmaConfianza;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;

final class ValidadorFirmaPdf implements SignatureValidatorContract
{
    private const DISCO_FALLBACK = 'public';

    private const TIMEOUT = 60;

    public function __construct(private readonly SignatureResultParser $parser) {}

    /**
     * @return array<string, mixed>
     */
    public function validar(string $rutaPdfFirmado, ?string $tokenBorrador = null): array
    {
        $disco = Storage::disk((string) config('certificados.disk', self::DISCO_FALLBACK));

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
            if (! preg_match('/^[0-9a-fA-F-]{36}$/', $tokenBorrador)) {
                throw new RuntimeException('El token del borrador no tiene un formato valido.');
            }

            $comando[] = '--token';
            $comando[] = $tokenBorrador;
        }

        $proceso = new Process($comando, null, $env);
        $timeout = (int) config('certificados.firma_timeout', self::TIMEOUT);
        $proceso->setTimeout($timeout > 0 ? $timeout : self::TIMEOUT);
        $proceso->run();

        $resultado = $this->parser->parse($proceso->getOutput());

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
        if (! config('certificados.verificar_cadena_confianza', true)) {
            return [];
        }

        $disco = Storage::disk((string) config('certificados.disk', self::DISCO_FALLBACK));

        return FirmaConfianza::query()
            ->where('activo', true)
            ->pluck('ruta_firma')
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
        $permitidos = [
            'PATH',
            'HOME',
            'USER',
            'LOGNAME',
            'LANG',
            'LC_ALL',
            'LC_CTYPE',
            'TMPDIR',
            'TEMP',
            'TMP',
            'PYTHONPATH',
            'PYTHONHOME',
            'VIRTUAL_ENV',
        ];

        $base = [];

        foreach ($permitidos as $clave) {
            $valor = $_SERVER[$clave] ?? $_ENV[$clave] ?? getenv($clave);

            if ($valor === false || $valor === null) {
                continue;
            }

            if (! is_scalar($valor)) {
                continue;
            }

            $base[$clave] = (string) $valor;
        }

        return array_merge($base, $extra);
    }
}
