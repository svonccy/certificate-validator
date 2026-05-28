<?php

declare(strict_types=1);

namespace App\Filament\Resources\Titulares\Tables;

use App\Filament\Resources\Titulares\TitularResource;
use App\Models\Titular;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TitularesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('dni')
                    ->label('DNI')
                    ->searchable()
                    ->sortable()
                    ->width(120),
                TextColumn::make('nombre_completo')
                    ->label('Nombre Completo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('certificados_count')
                    ->counts('certificados')
                    ->label('Nº Certificados')
                    ->badge()
                    ->color('info')
                    ->width(150),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->recordUrl(fn (Titular $record): string => TitularResource::getUrl('view', ['record' => $record]))
            ->groupedBulkActions([
                DeleteBulkAction::make()
                    ->visible(fn () => auth()->user()?->isAdmin()),
            ]);
    }
}
