<?php

declare(strict_types=1);

namespace App\Services\Certificados;

use App\Enums\EstadoCertificado;
use App\Models\Certificado;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class AdjuntarFirmadoService
{
    public function __construct(private readonly ValidadorFirmaContract $validador) {}

    public function ejecutar(Certificado $certificado, string $rutaPdfFirmado): AdjuntarFirmadoResultado
    {
        $disk = (string) config('certificados.disk', 'public');

        if (! Storage::disk($disk)->exists($rutaPdfFirmado)) {
            throw new RuntimeException('No se encontro el PDF firmado');
        }

        $tokenBorrador = $certificado->getAttribute('token_borrador');

        if (! $tokenBorrador) {
            throw new RuntimeException('Falta token del borrador');
        }

        $resultado = $this->validador->validar($rutaPdfFirmado, (string) $tokenBorrador);

        $borradorCoincide = (bool) Arr::get($resultado, 'borrador_coincide', true);
        $esValido = (bool) ($resultado['valido'] ?? false) && $borradorCoincide;
        $cadenaConfiable = (bool) Arr::get($resultado, 'firma.cadena_confiable', false);
        $fechaFirma = Arr::get($resultado, 'firma.fecha_firma');
        $fechaFirma = $fechaFirma ? Carbon::parse($fechaFirma) : null;
        $hashDocumento = Arr::get($resultado, 'firma.hash_pdf');

        if (! is_string($hashDocumento) || $hashDocumento === '') {
            throw new RuntimeException('Hash del PDF firmado no disponible');
        }

        $debeVerificarCadena = (bool) config('certificados.verificar_cadena_confianza', true);

        $estado = match (true) {
            $esValido && (! $debeVerificarCadena || $cadenaConfiable) => EstadoCertificado::Firmado,
            $esValido => EstadoCertificado::PendienteFirma,
            default => EstadoCertificado::Rechazado,
        };

        DB::transaction(function () use ($certificado, $rutaPdfFirmado, $estado, $esValido, $fechaFirma, $resultado, $hashDocumento): void {
            $certificado->forceFill([
                'ruta_pdf_firmado' => $rutaPdfFirmado,
                'estado' => $estado,
                'fecha_firma' => $fechaFirma,
            ])->save();

            $certificado->firmaDigital()->updateOrCreate(
                ['certificado_id' => $certificado->getKey()],
                [
                    'es_valida' => $esValido,
                    'fecha_firma' => $fechaFirma,
                    'serial' => Arr::get($resultado, 'firma.serial'),
                    'algoritmo' => Arr::get($resultado, 'firma.algoritmo'),
                    'hash_documento' => $hashDocumento,
                    'notario_nombre' => Arr::get($resultado, 'firmante.nombre'),
                    'notario_documento' => Arr::get($resultado, 'firmante.documento'),
                    'metadatos_completos' => $resultado,
                ]
            );
        });

        return $this->construirResultado($estado, $borradorCoincide, $resultado);
    }

    /**
     * @param  array<string, mixed>  $resultado
     */
    private function construirResultado(EstadoCertificado $estado, bool $borradorCoincide, array $resultado): AdjuntarFirmadoResultado
    {
        if (! $borradorCoincide) {
            return new AdjuntarFirmadoResultado(
                $estado,
                $borradorCoincide,
                'PDF firmado no coincide con el borrador',
                'El PDF firmado no contiene el token del borrador con QR.',
                'danger',
            );
        }

        $debeVerificarCadena = (bool) config('certificados.verificar_cadena_confianza', true);

        return match ($estado) {
            EstadoCertificado::Firmado => new AdjuntarFirmadoResultado(
                $estado,
                $borradorCoincide,
                'Firma valida',
                $debeVerificarCadena
                    ? 'El PDF firmado fue validado y la cadena es confiable.'
                    : 'El PDF firmado fue validado con éxito.',
                'success',
            ),
            EstadoCertificado::PendienteFirma => new AdjuntarFirmadoResultado(
                $estado,
                $borradorCoincide,
                'Firma valida con confianza pendiente',
                'La firma es integra, pero no se pudo validar la cadena de confianza.',
                'warning',
            ),
            EstadoCertificado::Rechazado => new AdjuntarFirmadoResultado(
                $estado,
                $borradorCoincide,
                'Firma rechazada',
                (string) ($resultado['motivo'] ?? 'La firma no es valida.'),
                'danger',
            ),
        };
    }
}
