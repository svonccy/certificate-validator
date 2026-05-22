<?php

declare(strict_types=1);

namespace App\Filament\Resources\Titulares\Pages;

use App\Filament\Resources\Titulares\TitularResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTitulares extends ListRecords
{
    protected static string $resource = TitularResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nuevo Titular'),
        ];
    }
}
