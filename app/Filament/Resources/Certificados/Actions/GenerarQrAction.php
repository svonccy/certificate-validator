<?php

declare(strict_types=1);

namespace App\Filament\Resources\Certificados\Actions;

use App\Enums\EstadoCertificado;
use App\Filament\Resources\Certificados\Schemas\CertificadoForm;
use App\Models\Certificado;
use App\Services\Certificados\ConfigurarQrBorradorService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;

class GenerarQrAction
{
    public static function makePageAction(): Action
    {
        return Action::make('generar_qr')
            ->label(fn (Action $action): string => $action->getRecord()?->ruta_pdf_borrador ? 'Re-generar QR' : 'Generar QR')
            ->icon('heroicon-o-qr-code')
            ->color(fn (Action $action): string => $action->getRecord()?->ruta_pdf_borrador ? 'gray' : 'primary')
            ->modalHeading('Configurar QR')
            ->modalSubmitActionLabel('Generar borrador')
            ->schema(CertificadoForm::esquemaQr())
            ->modalWidth(Width::Medium)
            ->fillForm(fn (Action $action): array => CertificadoForm::valoresPorDefectoQr($action->getRecord()))
            ->action(function (array $data, Action $action, ConfigurarQrBorradorService $service): void {
                $record = $action->getRecord();
                if (self::ejecutar($record, $data, $service)) {
                    $action->getLivewire()->redirect(request()->header('Referer'));
                }
            })
            ->visible(fn (Action $action): bool => (bool) $action->getRecord()?->getAttribute('ruta_pdf_original') && $action->getRecord()?->estado !== EstadoCertificado::Firmado);
    }

    public static function makeTableAction(): Action
    {
        return Action::make('generar_qr')
            ->label(fn (Certificado $record): string => $record->ruta_pdf_borrador ? 'Re-generar QR' : 'Generar QR')
            ->icon('heroicon-o-qr-code')
            ->color(fn (Certificado $record): string => $record->ruta_pdf_borrador ? 'gray' : 'warning')
            ->modalHeading('Configurar QR')
            ->modalSubmitActionLabel('Generar borrador')
            ->schema(CertificadoForm::esquemaQr())
            ->modalWidth(Width::Medium)
            ->fillForm(fn (Certificado $record): array => CertificadoForm::valoresPorDefectoQr($record))
            ->action(function (array $data, Certificado $record, ConfigurarQrBorradorService $service): void {
                self::ejecutar($record, $data, $service);
            })
            ->visible(fn (Certificado $record): bool => (bool) $record->getAttribute('ruta_pdf_original') && $record->estado !== EstadoCertificado::Firmado);
    }

    public static function ejecutar(Certificado $record, array $data, ConfigurarQrBorradorService $service): bool
    {
        if (! $record->getAttribute('ruta_pdf_original')) {
            Notification::make()
                ->title('No hay PDF original')
                ->body('Sube la plantilla PDF antes de generar el QR.')
                ->danger()
                ->send();

            return false;
        }

        try {
            $service->ejecutar($record, $data);
        } catch (\RuntimeException $exception) {
            Notification::make()
                ->title('No se pudo generar el QR')
                ->body($exception->getMessage())
                ->danger()
                ->send();

            return false;
        }

        Notification::make()
            ->title('QR generado')
            ->body('El borrador con QR esta listo para descargar.')
            ->success()
            ->send();

        return true;
    }
}
