<?php

declare(strict_types=1);

namespace App\Filament\Resources\Certificados\Pages;

use App\Enums\EstadoCertificado;
use App\Filament\Resources\Certificados\CertificadoResource;
use App\Filament\Resources\Certificados\Schemas\CertificadoForm;
use App\Services\Certificados\AdjuntarFirmadoService;
use App\Services\Certificados\ConfigurarQrBorradorService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Hugomyb\FilamentMediaAction\Actions\MediaAction;
use Illuminate\Support\Facades\Storage;

class ViewCertificado extends ViewRecord
{
    protected static string $resource = CertificadoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            MediaAction::make('previsualizar')
                ->label('Previsualizar PDF')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->media(fn (): ?string => ($path = $this->getRecord()->ruta_pdf_firmado ?? $this->getRecord()->ruta_pdf_borrador ?? $this->getRecord()->ruta_pdf_original) ? asset('storage/'.$path) : null)
                ->visible(fn (): bool => (bool) ($this->getRecord()->ruta_pdf_original ?? $this->getRecord()->ruta_pdf_borrador ?? $this->getRecord()->ruta_pdf_firmado)),

            Action::make('adjuntar_firmado')
                ->label('Adjuntar PDF firmado')
                ->icon('heroicon-o-arrow-up-tray')
                ->schema([
                    FileUpload::make('pdf_firmado')
                        ->label('PDF firmado')
                        ->acceptedFileTypes(['application/pdf'])
                        ->maxSize(10240)
                        ->disk(config('certificados.disk', 'public'))
                        ->directory(config('certificados.firmados_dir', 'certificados/firmados'))
                        ->required()
                        ->preventFilePathTampering(),
                ])
                ->action(function (array $data, AdjuntarFirmadoService $adjuntarFirmado): void {
                    $record = $this->getRecord();
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

                    $this->redirect(request()->header('Referer'));
                })
                ->visible(fn (): bool => (bool) $this->getRecord()->getAttribute('ruta_pdf_original')),

            Action::make('generar_qr')
                ->label('Generar QR')
                ->icon('heroicon-o-qr-code')
                ->modalHeading('Configurar QR')
                ->modalSubmitActionLabel('Generar borrador')
                ->schema(CertificadoForm::esquemaQr())
                ->fillForm(fn (): array => CertificadoForm::valoresPorDefectoQr($this->getRecord()))
                ->action(function (array $data, ConfigurarQrBorradorService $service): void {
                    $record = $this->getRecord();
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

                    $this->redirect(request()->header('Referer'));
                })
                ->visible(fn (): bool => (bool) $this->getRecord()->getAttribute('ruta_pdf_original')),

            Action::make('descargar')
                ->label(fn (): string => $this->getRecord()->estado === EstadoCertificado::Valido ? 'Descargar Firmado' : 'Descargar Borrador')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn (): string => route('certificados.descargar', $this->getRecord()))
                ->openUrlInNewTab()
                ->visible(fn (): bool => $this->getRecord()->estado === EstadoCertificado::Valido ? (bool) $this->getRecord()->ruta_pdf_firmado : (bool) $this->getRecord()->ruta_pdf_borrador)
                ->disabled(fn (): bool => ! Storage::disk((string) config('certificados.disk', 'public'))->exists((string) ($this->getRecord()->estado === EstadoCertificado::Valido ? $this->getRecord()->ruta_pdf_firmado : $this->getRecord()->ruta_pdf_borrador))),

            EditAction::make(),
        ];
    }
}
