<?php

declare(strict_types=1);

namespace App\Filament\Resources\Certificados\Tables;

use App\Enums\EstadoCertificado;
use App\Models\Certificado;
use App\Services\Certificados\GeneradorPdfQr;
use App\Services\Certificados\ValidadorFirmaPdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificadosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo_certificado')
                    ->label('Código')
                    ->searchable(),
                TextColumn::make('titular.dni')
                    ->label('DNI')
                    ->searchable(),
                TextColumn::make('titular.nombre_completo')
                    ->label('Nombre Completo')
                    ->searchable(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('adjuntar_firmado')
                        ->label('Adjuntar PDF firmado')
                        ->icon('heroicon-o-arrow-up-tray')
                        ->form([
                            FileUpload::make('pdf_firmado')
                                ->label('PDF firmado')
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(10240)
                                ->disk(config('certificados.disk', 'public'))
                                ->directory(config('certificados.firmados_dir', 'certificados/firmados'))
                                ->required()
                                ->preventFilePathTampering(),
                        ])
                        ->action(function (array $data, Certificado $record, ValidadorFirmaPdf $validador): void {
                            $disk = (string) config('certificados.disk', 'public');
                            $rutaPdfFirmado = $data['pdf_firmado'] ?? null;

                            if (! is_string($rutaPdfFirmado) || $rutaPdfFirmado === '') {
                                Notification::make()
                                    ->title('No se recibio el PDF firmado')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            if (! Storage::disk($disk)->exists($rutaPdfFirmado)) {
                                Notification::make()
                                    ->title('No se encontro el PDF firmado')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            $tokenBorrador = $record->getAttribute('token_borrador');

                            if (! $tokenBorrador) {
                                Notification::make()
                                    ->title('Falta token del borrador')
                                    ->body('Vuelve a generar el QR para vincular el borrador antes de validar.')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            try {
                                $resultado = $validador->validar($rutaPdfFirmado, (string) $tokenBorrador);
                            } catch (\RuntimeException $exception) {
                                Notification::make()
                                    ->title('No se pudo validar la firma')
                                    ->body($exception->getMessage())
                                    ->danger()
                                    ->send();

                                return;
                            }

                            $borradorCoincide = (bool) Arr::get($resultado, 'borrador_coincide', true);
                            $esValido = (bool) ($resultado['valido'] ?? false) && $borradorCoincide;
                            $cadenaConfiable = (bool) Arr::get($resultado, 'firma.cadena_confiable', false);
                            $fechaFirma = Arr::get($resultado, 'firma.fecha_firma');
                            $fechaFirma = $fechaFirma ? Carbon::parse($fechaFirma) : null;
                            $hashDocumento = Arr::get($resultado, 'firma.hash_pdf');

                            if (! is_string($hashDocumento) || $hashDocumento === '') {
                                Notification::make()
                                    ->title('Hash del PDF firmado no disponible')
                                    ->body('El validador no devolvio el hash del documento firmado.')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            $estado = match (true) {
                                $esValido && $cadenaConfiable => EstadoCertificado::Valido,
                                $esValido => EstadoCertificado::Pendiente,
                                default => EstadoCertificado::Rechazado,
                            };

                            DB::transaction(function () use ($record, $rutaPdfFirmado, $estado, $esValido, $fechaFirma, $resultado, $hashDocumento): void {
                                $record->forceFill([
                                    'ruta_pdf_firmado' => $rutaPdfFirmado,
                                    'estado' => $estado,
                                ])->save();

                                $record->firmaDigital()->updateOrCreate(
                                    ['certificado_id' => $record->getKey()],
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

                            $notificacion = Notification::make();

                            if (! $borradorCoincide) {
                                $notificacion
                                    ->title('PDF firmado no coincide con el borrador')
                                    ->body('El PDF firmado no contiene el token del borrador con QR.')
                                    ->color('danger');
                            } elseif ($estado === EstadoCertificado::Valido) {
                                $notificacion
                                    ->title('Firma valida')
                                    ->body('El PDF firmado fue validado y la cadena es confiable.')
                                    ->color('success');
                            } elseif ($estado === EstadoCertificado::Pendiente) {
                                $notificacion
                                    ->title('Firma valida con confianza pendiente')
                                    ->body('La firma es integra, pero no se pudo validar la cadena de confianza.')
                                    ->color('warning');
                            } else {
                                $notificacion
                                    ->title('Firma rechazada')
                                    ->body($resultado['motivo'] ?? 'La firma no es valida.')
                                    ->color('danger');
                            }

                            $notificacion->send();
                        })
                        ->visible(fn (Certificado $record): bool => (bool) $record->getAttribute('ruta_pdf_original')),

                    Action::make('generar_qr')
                        ->label('Generar QR')
                        ->icon('heroicon-o-qr-code')
                        ->requiresConfirmation()
                        ->action(function (Certificado $record, GeneradorPdfQr $generador): void {
                            if (! $record->getAttribute('ruta_pdf_original')) {
                                Notification::make()
                                    ->title('No hay PDF original')
                                    ->body('Sube la plantilla PDF antes de generar el QR.')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            $tokenBorrador = $record->getAttribute('token_borrador') ?: (string) Str::uuid();

                            try {
                                $rutaBorrador = $generador->generarBorrador($record, $tokenBorrador);
                            } catch (\RuntimeException $exception) {
                                Notification::make()
                                    ->title('No se pudo generar el QR')
                                    ->body($exception->getMessage())
                                    ->danger()
                                    ->send();

                                return;
                            }

                            $record->forceFill([
                                'ruta_pdf_borrador' => $rutaBorrador,
                                'token_borrador' => $tokenBorrador,
                            ])->save();

                            Notification::make()
                                ->title('QR generado')
                                ->body('El borrador con QR esta listo para descargar.')
                                ->success()
                                ->send();
                        })
                        ->visible(fn (Certificado $record): bool => (bool) $record->getAttribute('ruta_pdf_original')),

                    Action::make('descargar_borrador')
                        ->label('Descargar borrador')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(fn (Certificado $record): string => route('certificados.descargar-borrador', $record))
                        ->openUrlInNewTab()
                        ->visible(fn (Certificado $record): bool => (bool) $record->getAttribute('ruta_pdf_borrador'))
                        ->disabled(fn (Certificado $record): bool => ! Storage::disk((string) config('certificados.disk', 'public'))->exists((string) $record->getAttribute('ruta_pdf_borrador'))),

                    EditAction::make(),
                ]),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
