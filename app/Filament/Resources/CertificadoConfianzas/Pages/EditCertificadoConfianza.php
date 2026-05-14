<?php

declare(strict_types=1);

namespace App\Filament\Resources\CertificadoConfianzas\Pages;

use App\Filament\Resources\CertificadoConfianzas\CertificadoConfianzaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCertificadoConfianza extends EditRecord
{
    protected static string $resource = CertificadoConfianzaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
