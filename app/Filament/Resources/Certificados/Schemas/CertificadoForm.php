<?php

declare(strict_types=1);

namespace App\Filament\Resources\Certificados\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CertificadoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                TextInput::make('codigo_certificado')
                                    ->label('Código del certificado')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('dni_titular')
                                    ->label('DNI')
                                    ->required()
                                    ->maxLength(8),
                                TextInput::make('nombre_titular')
                                    ->label('Nombre Completo')
                                    ->required()
                                    ->maxLength(255),
                                DatePicker::make('fecha_emision')
                                    ->label('Fecha de Emision')
                                    ->required()
                                    ->default(now()),
                                FileUpload::make('ruta_pdf_original')
                                    ->Label('Certificado en PDF')
                                    ->disk('public')
                                    ->directory('certificados/plantillas')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->openable(true),
                                ToggleButtons::make('estado')
                                    ->label('Estado')
                                    ->options([
                                        'pendiente' => 'Pendiente',
                                        'firmado' => 'Firmado',
                                    ])
                                    ->colors([
                                        'pendiente' => 'warning',
                                        'firmado' => 'success',
                                    ])
                                    ->inline()
                                    ->grouped()
                            ])->columns(2),
                    ])->columnSpanFull(),
            ]);
    }
}
