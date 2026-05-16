<?php

declare(strict_types=1);

namespace App\Filament\Resources\FirmasConfianza\Pages;

use App\Filament\Resources\FirmasConfianza\FirmaConfianzaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFirmasConfianza extends ListRecords
{
    protected static string $resource = FirmaConfianzaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
