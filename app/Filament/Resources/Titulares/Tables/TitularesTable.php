<?php

declare(strict_types=1);

namespace App\Filament\Resources\Titulares\Tables;

use App\Filament\Resources\Titulares\TitularResource;
use App\Models\Titular;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TitularesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('certificados'))
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
                    ->width(150)
                    ->alignCenter()
                    ->size('md')
                    ->url(fn (Titular $record): string => route('filament.admin.resources.documentos.certificados.index', [
                        'tableSearch' => $record->dni,
                    ]))
                    ->tooltip(fn (Titular $record): string => $record->certificados->isEmpty()
                        ? 'Sin certificados'
                        : $record->certificados->pluck('codigo_certificado')->implode(', ')
                    ),
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
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()->color('info'),
                ]),
            ])
            ->recordUrl(fn (Titular $record): string => TitularResource::getUrl('view', ['record' => $record]))
            ->groupedBulkActions([
                DeleteBulkAction::make()
                    ->visible(fn () => auth()->user()?->isAdmin()),
            ]);
    }
}
