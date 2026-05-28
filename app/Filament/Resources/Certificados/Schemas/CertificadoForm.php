<?php

declare(strict_types=1);

namespace App\Filament\Resources\Certificados\Schemas;

use App\Enums\PresetQr;
use App\Models\Certificado;
use App\Rules\CleanPdfTemplateRule;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\GridDirection;
use Illuminate\Database\Eloquent\Model;

class CertificadoForm
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
                                        TextInput::make('codigo_certificado')
                                            ->label('Código del certificado')
                                            ->required()
                                            ->maxLength(255),
                                        Select::make('titular_id')
                                            ->label('Titular del Certificado')
                                            ->relationship('titular', 'nombre_completo')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->dni} - {$record->nombre_completo}")
                                            ->createOptionForm([
                                                TextInput::make('dni')
                                                    ->label('DNI')
                                                    ->required()
                                                    ->maxLength(8)
                                                    ->unique('titulares', 'dni'),
                                                TextInput::make('nombre_completo')
                                                    ->label('Nombre Completo')
                                                    ->required()
                                                    ->maxLength(255),
                                            ]),
                                    ]),
                            ])
                            ->columnSpan(1),

                        // Right Column (Main content) - Column Span 2
                        Group::make()
                            ->schema([
                                Section::make('Documento PDF (Plantilla Original)')
                                    ->schema([
                                        FileUpload::make('ruta_pdf_original')
                                            ->label('Archivo PDF original')
                                            ->disk('public')
                                            ->directory('certificados/plantillas')
                                            ->acceptedFileTypes(['application/pdf'])
                                            ->openable(true)
                                            ->rules([
                                                new CleanPdfTemplateRule,
                                            ]),
                                    ]),
                            ])
                            ->columnSpan(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    /**
     * @return array<int, mixed>
     */
    public static function esquemaQr(): array
    {
        return [
            Radio::make('qr_preset_grid')
                ->label('Posición del QR')
                ->options(array_map(fn () => '', PresetQr::opcionesCuadricula()))
                ->columns(5)
                ->gridDirection(GridDirection::Row)
                ->required(fn (Get $get): bool => ! (bool) $get('qr_manual'))
                ->hidden(fn (Get $get): bool => (bool) $get('qr_manual'))
                ->live()
                ->columnSpanFull(),
            Toggle::make('qr_manual')
                ->label('Manual')
                ->live(),
            TextInput::make('qr_lado')
                ->label('Lado del QR (mm)')
                ->numeric()
                ->minValue(10)
                ->required(),
            //            TextInput::make('qr_pagina')
            //                ->label('Página')
            //                ->numeric()
            //                ->minValue(1)
            //                ->required(),
            TextInput::make('qr_x')
                ->label('Coordenada X (mm)')
                ->numeric()
                ->required(fn (Get $get): bool => (bool) $get('qr_manual'))
                ->hidden(fn (Get $get): bool => ! (bool) $get('qr_manual')),
            TextInput::make('qr_y')
                ->label('Coordenada Y (mm)')
                ->numeric()
                ->required(fn (Get $get): bool => (bool) $get('qr_manual'))
                ->hidden(fn (Get $get): bool => ! (bool) $get('qr_manual')),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function valoresPorDefectoQr(Certificado $record): array
    {
        $defaults = config('certificados.defaults', []);
        $defaults = is_array($defaults) ? $defaults : [];
        $datosQr = $record->getAttribute('datos_qr');
        $datosQr = is_array($datosQr) ? $datosQr : [];

        $presetValor = (string) ($datosQr['preset'] ?? $defaults['preset'] ?? PresetQr::Superior1->value);
        $preset = PresetQr::desdeValor($presetValor);
        $esManual = $preset === PresetQr::Manual;

        return [
            'qr_preset_grid' => $esManual ? PresetQr::Superior1->value : $preset->value,
            'qr_manual' => $esManual,
            'qr_lado' => $datosQr['lado'] ?? $defaults['lado'] ?? 30,
            'qr_x' => $datosQr['x'] ?? $defaults['x'] ?? null,
            'qr_y' => $datosQr['y'] ?? $defaults['y'] ?? null,
            'qr_pagina' => $record->getAttribute('qr_pagina') ?? ($defaults['pagina'] ?? 1),
        ];
    }
}
