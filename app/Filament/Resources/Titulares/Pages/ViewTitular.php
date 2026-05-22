<?php

declare(strict_types=1);

namespace App\Filament\Resources\Titulares\Pages;

use App\Filament\Resources\Titulares\TitularResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTitular extends ViewRecord
{
    protected static string $resource = TitularResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
