<?php

declare(strict_types=1);

namespace App\Filament\Resources\Titulares\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TitularInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        // Left Column: Holder Information
                        Group::make()
                            ->schema([
                                Section::make('Datos del Titular')
                                    ->schema([
                                        TextEntry::make('dni')
                                            ->label('DNI'),
                                        TextEntry::make('nombre_completo')
                                            ->label('Nombre Completo'),
                                        TextEntry::make('created_at')
                                            ->label('Registrado el')
                                            ->dateTime('d/m/Y H:i:s'),
                                        TextEntry::make('updated_at')
                                            ->label('Actualizado el')
                                            ->dateTime('d/m/Y H:i:s'),
                                    ]),
                            ])
                            ->columnSpan(1),

                        // Right Column: Certificates list
                        Group::make()
                            ->schema([
                                Section::make('Certificados Asociados')
                                    ->description('Lista de certificados emitidos para este titular.')
                                    ->schema([
                                        RepeatableEntry::make('certificados')
                                            ->label('')
                                            ->schema([
                                                TextEntry::make('codigo_certificado')
                                                    ->label('Código del Certificado'),
                                                TextEntry::make('estado')
                                                    ->label('Estado')
                                                    ->badge(),
                                                TextEntry::make('fecha_emision')
                                                    ->label('Fecha de Emisión')
                                                    ->date('d/m/Y'),
                                            ])
                                            ->columns(3),
                                    ]),
                            ])
                            ->columnSpan(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
