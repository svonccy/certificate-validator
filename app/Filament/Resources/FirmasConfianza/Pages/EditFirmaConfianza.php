<?php

declare(strict_types=1);

namespace App\Filament\Resources\FirmasConfianza\Pages;

use App\Filament\Resources\FirmasConfianza\FirmaConfianzaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFirmaConfianza extends EditRecord
{
    protected static string $resource = FirmaConfianzaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
