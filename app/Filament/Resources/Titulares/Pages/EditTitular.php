<?php

declare(strict_types=1);

namespace App\Filament\Resources\Titulares\Pages;

use App\Filament\Resources\Titulares\TitularResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTitular extends EditRecord
{
    protected static string $resource = TitularResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
