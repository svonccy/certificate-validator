<?php

declare(strict_types=1);

namespace App\Filament\Resources\Certificados\Tables;

use App\Enums\EstadoCertificado;
use App\Filament\Resources\Certificados\Actions\GenerarQrAction;
use App\Filament\Resources\Certificados\CertificadoResource;
use App\Models\Certificado;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class CertificadosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo_certificado')
                    ->label('Código')
                    ->width(100)
                    ->searchable(),
                TextColumn::make('titular.dni')
                    ->label('DNI')
                    ->width(100)
                    ->searchable(),
                TextColumn::make('titular.nombre_completo')
                    ->label('Nombre Completo')
                    ->searchable(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->width(100)
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->width(100)
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Modificado')
                    ->width(100)
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    GenerarQrAction::makeTableAction(),

                    Action::make('descargar')
                        ->label(fn (Certificado $record): string => $record->estado === EstadoCertificado::Firmado ? 'Descargar Firmado' : 'Descargar Borrador')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(fn (Certificado $record): string => route('certificados.descargar', $record))
                        ->openUrlInNewTab()
                        ->visible(fn (Certificado $record): bool => $record->estado === EstadoCertificado::Firmado ? (bool) $record->ruta_pdf_firmado : (bool) $record->ruta_pdf_borrador)
                        ->disabled(fn (Certificado $record): bool => ! Storage::disk((string) config('certificados.disk', 'public'))->exists((string) ($record->estado === EstadoCertificado::Firmado ? $record->ruta_pdf_firmado : $record->ruta_pdf_borrador))),

                    ViewAction::make()
                        ->color('gray'),

                    EditAction::make()
                        ->color('info')
                        ->visible(fn (Certificado $record): bool => $record->estado !== EstadoCertificado::Firmado),

                    DeleteAction::make()
                        ->color('danger')
                        ->visible(fn () => auth()->user()?->isAdmin()),
                ]),
            ])
            ->recordUrl(fn (Certificado $record): string => CertificadoResource::getUrl('view', ['record' => $record]))
            ->groupedBulkActions([
                DeleteBulkAction::make()
                    ->visible(fn () => auth()->user()?->isAdmin()),
            ]);
    }
}
