<?php

declare(strict_types=1);

namespace App\Filament\Resources\Certificados\Pages;

use App\Filament\Resources\Certificados\CertificadoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCertificado extends CreateRecord
{
    protected static string $resource = CertificadoResource::class;
}
