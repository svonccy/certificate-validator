<?php

declare(strict_types=1);

namespace App\Filament\Resources\Certificados\Schemas;

use App\Models\Certificado;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

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
                                        TextEntry::make('estado')
                                            ->label('Estado')
                                            ->badge(),
                                        TextEntry::make('fecha_firma')
                                            ->label('Fecha de Firma')
                                            ->placeholder('Pendiente de firma')
                                            ->dateTime('d/m/Y H:i'),
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
                                Section::make(fn (Certificado $record): string => match (true) {
                                    (bool) $record->ruta_pdf_firmado => 'Previsualización: Documento Firmado',
                                    (bool) $record->ruta_pdf_borrador => 'Previsualización: Borrador (con QR)',
                                    (bool) $record->ruta_pdf_original => 'Previsualización: Plantilla Original',
                                    default => 'Previsualización no disponible',
                                })
                                    ->description(fn (Certificado $record): string => match (true) {
                                        (bool) $record->ruta_pdf_firmado => 'Mostrando el documento final firmado digitalmente.',
                                        (bool) $record->ruta_pdf_borrador => 'Mostrando el borrador generado con el código QR incrustado.',
                                        (bool) $record->ruta_pdf_original => 'Mostrando el archivo PDF de plantilla original sin QR ni firmas.',
                                        default => 'Suba la plantilla original del certificado para activar la previsualización.',
                                    })
                                    ->icon(fn (Certificado $record): string => match (true) {
                                        (bool) $record->ruta_pdf_firmado => 'heroicon-o-check-badge',
                                        (bool) $record->ruta_pdf_borrador => 'heroicon-o-qr-code',
                                        (bool) $record->ruta_pdf_original => 'heroicon-o-document',
                                        default => 'heroicon-o-document-minus',
                                    })
                                    ->schema([
                                        View::make('pdf_viewer')
                                            ->view('filament.infolists.components.pdf-viewer')
                                            ->viewData(fn (Certificado $record): array => [
                                                'path' => match (true) {
                                                    (bool) $record->ruta_pdf_firmado => $record->ruta_pdf_firmado,
                                                    (bool) $record->ruta_pdf_borrador => $record->ruta_pdf_borrador,
                                                    (bool) $record->ruta_pdf_original => $record->ruta_pdf_original,
                                                    default => null,
                                                },
                                            ]),
                                    ])
                                    ->columnSpanFull(),
                            ])
                            ->columnSpan(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
