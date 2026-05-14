<?php

declare(strict_types=1);

namespace App\Filament\Resources\Certificados\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CertificadoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('dni_titular')
                    ->label('DNI del titular')
                    ->required()
                    ->maxLength(8),
                TextInput::make('nombre_titular')
                    ->label('Nombres del titular')
                    ->required()
                    ->maxLength(255),
                TextInput::make('tipo_certificado')
                    ->label('Tipo de certificado')
                    ->required()
                    ->maxLength(255),
                FileUpload::make('ruta_pdf_original')
                    ->label('Plantilla PDF original')
                    ->acceptedFileTypes(['application/pdf'])
                    ->disk('local')
                    ->directory('certificados/originales')
                    ->required()
                    ->preventFilePathTampering(),
            ]);
    }
}
