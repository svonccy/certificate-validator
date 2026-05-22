<?php

declare(strict_types=1);

namespace App\Filament\Resources\Titulares\Pages;

use App\Filament\Resources\Titulares\TitularResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTitular extends CreateRecord
{
    protected static string $resource = TitularResource::class;
}
