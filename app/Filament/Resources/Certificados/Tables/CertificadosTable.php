<?php

declare(strict_types=1);

namespace App\Filament\Resources\Certificados\Tables;

use App\Enums\EstadoCertificado;
use App\Filament\Resources\Certificados\CertificadoResource;
use App\Filament\Resources\Certificados\Schemas\CertificadoForm;
use App\Models\Certificado;
use App\Services\Certificados\ConfigurarQrBorradorService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

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
                    Action::make('generar_qr')
                        ->label('Generar QR')
                        ->icon('heroicon-o-qr-code')
                        ->modalHeading('Configurar QR')
                        ->modalSubmitActionLabel('Generar borrador')
                        ->schema(CertificadoForm::esquemaQr())
                        ->fillForm(fn (Certificado $record): array => CertificadoForm::valoresPorDefectoQr($record))
                        ->action(function (array $data, Certificado $record, ConfigurarQrBorradorService $service): void {
                            if (! $record->getAttribute('ruta_pdf_original')) {
                                Notification::make()
                                    ->title('No hay PDF original')
                                    ->body('Sube la plantilla PDF antes de generar el QR.')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            try {
                                $service->ejecutar($record, $data);
                            } catch (\RuntimeException $exception) {
                                Notification::make()
                                    ->title('No se pudo generar el QR')
                                    ->body($exception->getMessage())
                                    ->danger()
                                    ->send();

                                return;
                            }

                            Notification::make()
                                ->title('QR generado')
                                ->body('El borrador con QR esta listo para descargar.')
                                ->success()
                                ->send();
                        })
                        ->visible(fn (Certificado $record): bool => (bool) $record->getAttribute('ruta_pdf_original')),

                    Action::make('descargar')
                        ->label(fn (Certificado $record): string => $record->estado === EstadoCertificado::Valido ? 'Descargar Firmado' : 'Descargar Borrador')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(fn (Certificado $record): string => route('certificados.descargar', $record))
                        ->openUrlInNewTab()
                        ->visible(fn (Certificado $record): bool => $record->estado === EstadoCertificado::Valido ? (bool) $record->ruta_pdf_firmado : (bool) $record->ruta_pdf_borrador)
                        ->disabled(fn (Certificado $record): bool => ! Storage::disk((string) config('certificados.disk', 'public'))->exists((string) ($record->estado === EstadoCertificado::Valido ? $record->ruta_pdf_firmado : $record->ruta_pdf_borrador))),

                    ViewAction::make()
                        ->color('gray'),

                    EditAction::make()
                        ->color('info'),

                    DeleteAction::make()
                        ->color('danger'),
                ]),
            ])
            ->recordUrl(fn (Certificado $record): string => CertificadoResource::getUrl('view', ['record' => $record]))
            ->groupedBulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
