<?php

declare(strict_types=1);

namespace App\Filament\Resources\CertificadoConfianzas\Pages;

use App\Filament\Resources\CertificadoConfianzas\CertificadoConfianzaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCertificadoConfianzas extends ListRecords
{
    protected static string $resource = CertificadoConfianzaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
