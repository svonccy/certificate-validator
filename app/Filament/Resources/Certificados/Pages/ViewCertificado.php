<?php

declare(strict_types=1);

namespace App\Filament\Resources\Certificados\Pages;

use App\Enums\EstadoCertificado;
use App\Filament\Resources\Certificados\Actions\GenerarQrAction;
use App\Filament\Resources\Certificados\CertificadoResource;
use App\Services\Certificados\AdjuntarFirmadoService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Storage;

class ViewCertificado extends ViewRecord
{
    protected static string $resource = CertificadoResource::class;

    protected function getHeaderActions(): array
    {
        return [
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
                ->visible(fn (): bool => (bool) $this->getRecord()->ruta_pdf_original && (bool) $this->getRecord()->ruta_pdf_borrador && $this->getRecord()->estado !== EstadoCertificado::Firmado),

            GenerarQrAction::makePageAction(),

            Action::make('descargar')
                ->label(fn (): string => $this->getRecord()->estado === EstadoCertificado::Firmado ? 'Descargar Firmado' : 'Descargar Borrador')
                ->icon('heroicon-o-arrow-down-tray')
                ->color(fn (): string => $this->getRecord()->estado === EstadoCertificado::Firmado ? 'success' : 'warning')
                ->url(fn (): string => route('certificados.descargar', $this->getRecord()))
                ->openUrlInNewTab()
                ->visible(fn (): bool => $this->getRecord()->estado === EstadoCertificado::Firmado ? (bool) $this->getRecord()->ruta_pdf_firmado : (bool) $this->getRecord()->ruta_pdf_borrador)
                ->disabled(fn (): bool => ! Storage::disk((string) config('certificados.disk', 'public'))->exists((string) ($this->getRecord()->estado === EstadoCertificado::Firmado ? $this->getRecord()->ruta_pdf_firmado : $this->getRecord()->ruta_pdf_borrador))),

            EditAction::make()
                ->color('info')
                ->visible(fn (): bool => $this->getRecord()->estado !== EstadoCertificado::Firmado),
        ];
    }
}
