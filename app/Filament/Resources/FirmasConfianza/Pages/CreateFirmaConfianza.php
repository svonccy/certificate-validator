<?php

declare(strict_types=1);

namespace App\Filament\Resources\FirmasConfianza\Pages;

use App\Filament\Resources\FirmasConfianza\FirmaConfianzaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFirmaConfianza extends CreateRecord
{
    protected static string $resource = FirmaConfianzaResource::class;
}
