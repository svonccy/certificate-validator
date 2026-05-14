<?php

declare(strict_types=1);

namespace App\Filament\Resources\CertificadoConfianzas\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CertificadoConfianzaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),
                FileUpload::make('ruta_certificado')
                    ->label('Certificado (CA)')
                    ->acceptedFileTypes([
                        'application/x-x509-ca-cert',
                        'application/pkix-cert',
                        'application/x-pem-file',
                        'application/octet-stream',
                    ])
                    ->disk('local')
                    ->directory('certificados/trust-roots')
                    ->required()
                    ->preventFilePathTampering(),
                Toggle::make('activo')
                    ->label('Activo')
                    ->default(true),
            ]);
    }
}
