<?php

declare(strict_types=1);

namespace App\Filament\Resources\Certificados\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CertificadosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('dni_titular')
                    ->label('DNI')
                    ->searchable(),
                TextColumn::make('nombre_titular')
                    ->label('Titular')
                    ->searchable(),
                TextColumn::make('tipo_certificado')
                    ->label('Tipo')
                    ->searchable(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (?string $estado): string => match ($estado ?? 'PENDIENTE') {
                        'PENDIENTE' => 'warning',
                        'VALIDO' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $estado): string => match ($estado ?? 'PENDIENTE') {
                        'PENDIENTE' => 'Pendiente',
                        'VALIDO' => 'Valido',
                        default => (string) $estado,
                    }),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
