<?php

declare(strict_types=1);

namespace App\Filament\Resources\Certificados\Tables;

use App\Models\Certificado;
use App\Services\Certificados\AdjuntarFirmadoService;
use App\Services\Certificados\GeneradorPdfQr;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
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
                        ->action(function (array $data, Certificado $record, AdjuntarFirmadoService $adjuntarFirmado): void {
                            $rutaPdfFirmado = $data['pdf_firmado'] ?? null;

                            if (! is_string($rutaPdfFirmado) || $rutaPdfFirmado === '') {
                                Notification::make()
                                    ->title('No se recibio el PDF firmado')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            try {
                                $resultadoAdjunto = $adjuntarFirmado->ejecutar($record, $rutaPdfFirmado);
                            } catch (\RuntimeException $exception) {
                                Notification::make()
                                    ->title('No se pudo validar la firma')
                                    ->body($exception->getMessage())
                                    ->danger()
                                    ->send();

                                return;
                            }

                            Notification::make()
                                ->title($resultadoAdjunto->titulo)
                                ->body($resultadoAdjunto->mensaje)
                                ->color($resultadoAdjunto->color)
                                ->send();
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
