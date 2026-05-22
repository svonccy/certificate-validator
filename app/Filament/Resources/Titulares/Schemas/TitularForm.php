<?php

declare(strict_types=1);

namespace App\Filament\Resources\Titulares\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TitularForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Group::make()
                            ->schema([
                                Section::make('Identificación')
                                    ->schema([
                                        TextInput::make('dni')
                                            ->label('DNI')
                                            ->required()
                                            ->length(8)
                                            ->numeric()
                                            ->unique('titulares', 'dni', ignoreRecord: true)
                                            ->placeholder('Ej. 12345678'),
                                    ]),
                            ])
                            ->columnSpan(1),

                        Group::make()
                            ->schema([
                                Section::make('Información Personal')
                                    ->schema([
                                        TextInput::make('nombre_completo')
                                            ->label('Nombre Completo')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Nombres y Apellidos'),
                                    ]),
                            ])
                            ->columnSpan(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
