<?php

declare(strict_types=1);

namespace App\Filament\Resources\Certificados\Tables;

use App\Enums\PresetQr;
use App\Filament\Resources\Certificados\CertificadoResource;
use App\Models\Certificado;
use App\Services\Certificados\AdjuntarFirmadoService;
use App\Services\Certificados\GeneradorPdfQr;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\GridDirection;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Hugomyb\FilamentMediaAction\Actions\MediaAction;
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
                    ->width(100)
                    ->searchable(),
                TextColumn::make('titular.dni')
                    ->label('DNI')
                    ->width(100)
                    ->searchable(),
                TextColumn::make('titular.nombre_completo')
                    ->label('Nombre Completo')
                    ->searchable(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->width(100)
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->width(100)
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Modificado')
                    ->width(100)
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    MediaAction::make('previsualizar')
                        ->label('Previsualizar PDF')
                        ->icon('heroicon-o-eye')
                        ->media(fn (Certificado $record) => asset('storage/'.($record->ruta_pdf_firmado ?? $record->ruta_pdf_borrador ?? $record->ruta_pdf_original)))
                        ->visible(fn (Certificado $record): bool => (bool) ($record->ruta_pdf_original ?? $record->ruta_pdf_borrador ?? $record->ruta_pdf_firmado)),

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
                        ->modalHeading('Configurar QR')
                        ->modalSubmitActionLabel('Generar borrador')
                        ->form([
                            ToggleButtons::make('qr_preset_grid')
                                ->label('Posición del QR')
                                ->options(PresetQr::opcionesCuadricula())
                                ->columns(5)
                                ->gridDirection(GridDirection::Row)
                                ->required(fn (Get $get): bool => ! (bool) $get('qr_manual'))
                                ->hidden(fn (Get $get): bool => (bool) $get('qr_manual'))
                                ->live()
                                ->columnSpanFull(),
                            Toggle::make('qr_manual')
                                ->label('Manual')
                                ->live(),
                            TextInput::make('qr_lado')
                                ->label('Lado del QR (mm)')
                                ->numeric()
                                ->minValue(10)
                                ->required(),
                            TextInput::make('qr_pagina')
                                ->label('Página')
                                ->numeric()
                                ->minValue(1)
                                ->required(),
                            TextInput::make('qr_x')
                                ->label('Coordenada X (mm)')
                                ->numeric()
                                ->required(fn (Get $get): bool => (bool) $get('qr_manual'))
                                ->hidden(fn (Get $get): bool => ! (bool) $get('qr_manual')),
                            TextInput::make('qr_y')
                                ->label('Coordenada Y (mm)')
                                ->numeric()
                                ->required(fn (Get $get): bool => (bool) $get('qr_manual'))
                                ->hidden(fn (Get $get): bool => ! (bool) $get('qr_manual')),
                        ])
                        ->fillForm(function (Certificado $record): array {
                            $defaults = config('certificados.defaults', []);
                            $defaults = is_array($defaults) ? $defaults : [];
                            $datosQr = $record->getAttribute('datos_qr');
                            $datosQr = is_array($datosQr) ? $datosQr : [];

                            $presetValor = (string) ($datosQr['preset'] ?? $defaults['preset'] ?? PresetQr::Superior1->value);
                            $preset = PresetQr::desdeValor($presetValor);
                            $esManual = $preset === PresetQr::Manual;

                            return [
                                'qr_preset_grid' => $esManual ? PresetQr::Superior1->value : $preset->value,
                                'qr_manual' => $esManual,
                                'qr_lado' => $datosQr['lado'] ?? $defaults['lado'] ?? 30,
                                'qr_x' => $datosQr['x'] ?? $defaults['x'] ?? null,
                                'qr_y' => $datosQr['y'] ?? $defaults['y'] ?? null,
                                'qr_pagina' => $record->getAttribute('qr_pagina') ?? ($defaults['pagina'] ?? 1),
                            ];
                        })
                        ->action(function (array $data, Certificado $record, GeneradorPdfQr $generador): void {
                            if (! $record->getAttribute('ruta_pdf_original')) {
                                Notification::make()
                                    ->title('No hay PDF original')
                                    ->body('Sube la plantilla PDF antes de generar el QR.')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            $defaults = config('certificados.defaults', []);
                            $defaults = is_array($defaults) ? $defaults : [];

                            $tokenBorrador = $record->getAttribute('token_borrador') ?: (string) Str::uuid();
                            $usarManual = (bool) ($data['qr_manual'] ?? false);
                            $presetValor = $usarManual
                                ? PresetQr::Manual->value
                                : (string) ($data['qr_preset_grid'] ?? $defaults['preset'] ?? PresetQr::Superior1->value);
                            $preset = PresetQr::desdeValor($presetValor);
                            $lado = is_numeric($data['qr_lado'] ?? null) ? (float) $data['qr_lado'] : null;
                            $x = is_numeric($data['qr_x'] ?? null) ? (float) $data['qr_x'] : null;
                            $y = is_numeric($data['qr_y'] ?? null) ? (float) $data['qr_y'] : null;
                            $pagina = is_numeric($data['qr_pagina'] ?? null)
                                ? max((int) $data['qr_pagina'], 1)
                                : (int) ($defaults['pagina'] ?? 1);

                            if ($preset !== PresetQr::Manual) {
                                $x = null;
                                $y = null;
                            }

                            $datosQr = array_filter([
                                'preset' => $preset->value,
                                'lado' => $lado,
                                'x' => $x,
                                'y' => $y,
                            ], static fn ($valor): bool => $valor !== null);

                            $record->forceFill([
                                'datos_qr' => $datosQr,
                                'qr_pagina' => $pagina,
                                'token_borrador' => $tokenBorrador,
                            ]);

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
            ->recordUrl(fn (Certificado $record): string => CertificadoResource::getUrl('view', ['record' => $record]))
            ->groupedBulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
