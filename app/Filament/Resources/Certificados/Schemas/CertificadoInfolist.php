<?php

declare(strict_types=1);

namespace App\Filament\Resources\Certificados\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Hugomyb\FilamentMediaAction\Actions\MediaAction;

class CertificadoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        // Left Column (Sidebar) - Column Span 1
                        Group::make()
                            ->schema([
                                Section::make('Detalles del Certificado')
                                    ->schema([
                                        TextEntry::make('codigo_certificado')
                                            ->label('Código del certificado'),
                                        TextEntry::make('titular.nombre_completo')
                                            ->label('Titular')
                                            ->formatStateUsing(fn ($record) => "{$record->titular->dni} - {$record->titular->nombre_completo}"),
                                        TextEntry::make('fecha_emision')
                                            ->label('Fecha de Emisión')
                                            ->date('d/m/Y'),
                                        TextEntry::make('estado')
                                            ->label('Estado')
                                            ->badge(),
                                        TextEntry::make('created_at')
                                            ->label('Creado el')
                                            ->dateTime('d/m/Y H:i:s'),
                                        TextEntry::make('updated_at')
                                            ->label('Actualizado el')
                                            ->dateTime('d/m/Y H:i:s'),
                                    ]),
                            ])
                            ->columnSpan(1),

                        // Right Column (Main content) - Column Span 2
                        Group::make()
                            ->schema([
                                Section::make('Documentos Disponibles')
                                    ->description('Previsualiza o descarga las diferentes versiones del PDF del certificado.')
                                    ->schema([
                                        Actions::make([
                                            MediaAction::make('previsualizar_original')
                                                ->label('Previsualizar Plantilla')
                                                ->icon('heroicon-o-document')
                                                ->color('gray')
                                                ->media(fn ($record) => $record->ruta_pdf_original ? asset('storage/'.$record->ruta_pdf_original) : null)
                                                ->visible(fn ($record) => (bool) $record->ruta_pdf_original),

                                            MediaAction::make('previsualizar_borrador')
                                                ->label('Previsualizar Borrador')
                                                ->icon('heroicon-o-qr-code')
                                                ->color('warning')
                                                ->media(fn ($record) => $record->ruta_pdf_borrador ? asset('storage/'.$record->ruta_pdf_borrador) : null)
                                                ->visible(fn ($record) => (bool) $record->ruta_pdf_borrador),

                                            MediaAction::make('previsualizar_firmado')
                                                ->label('Previsualizar PDF Firmado')
                                                ->icon('heroicon-o-check-badge')
                                                ->color('success')
                                                ->media(fn ($record) => $record->ruta_pdf_firmado ? asset('storage/'.$record->ruta_pdf_firmado) : null)
                                                ->visible(fn ($record) => (bool) $record->ruta_pdf_firmado),
                                        ]),
                                    ]),
                            ])
                            ->columnSpan(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
