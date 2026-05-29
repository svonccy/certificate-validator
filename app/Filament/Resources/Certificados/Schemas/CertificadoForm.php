<?php

declare(strict_types=1);

namespace App\Filament\Resources\Certificados\Schemas;

use App\DTO\DatosQr;
use App\Enums\PresetQr;
use App\Models\Certificado;
use App\Rules\CleanPdfTemplateRule;
use App\Services\Certificados\CalculadorPosicionQr;
use App\Services\Certificados\EditorPdfFpdi;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Slider;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\GridDirection;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
                                            ])
                                            ->createOptionAction(fn ($action) => $action->modalHeading('Nuevo Titular')->modalWidth(Width::Medium)),
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
    public static function esquemaQr(?Certificado $record = null): array
    {
        $width = 210.0;
        $height = 297.0;

        if ($record && $record->ruta_pdf_original) {
            try {
                $disco = Storage::disk((string) config('certificados.disk', 'public'));
                if ($disco->exists((string) $record->ruta_pdf_original)) {
                    $editor = new EditorPdfFpdi;
                    $editor->cargarOrigen($disco->path((string) $record->ruta_pdf_original));
                    $tamano = $editor->clonarPagina((int) ($record->qr_pagina ?? 1));
                    $width = (float) $tamano['width'];
                    $height = (float) $tamano['height'];
                }
            } catch (\Throwable $e) {
                // Fallback to A4 if error
            }
        }

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
            Slider::make('qr_x')
                ->label('Coordenada X (mm)')
                ->range(
                    minValue: 0,
                    maxValue: fn (Get $get): int => (int) round(max(0.0, $width - (float) ($get('qr_lado') ?? 30.0)))
                )
                ->step(1)
                ->required(fn (Get $get): bool => (bool) $get('qr_manual'))
                ->hidden(fn (Get $get): bool => ! (bool) $get('qr_manual'))
                ->live(),
            Slider::make('qr_y')
                ->label('Coordenada Y (mm)')
                ->range(
                    minValue: 0,
                    maxValue: fn (Get $get): int => (int) round(max(0.0, $height - ((float) ($get('qr_lado') ?? 30.0) + 5.0)))
                )
                ->step(1)
                ->required(fn (Get $get): bool => (bool) $get('qr_manual'))
                ->hidden(fn (Get $get): bool => ! (bool) $get('qr_manual'))
                ->live(),
            Toggle::make('qr_manual')
                ->label('Manual')
                ->helperText('El punto de origen (0,0) es la esquina superior izquierda del documento PDF.')
                ->live()
                ->afterStateUpdated(function (Get $get, Set $set) use ($width, $height) {
                    if ($get('qr_manual')) {
                        $presetValor = $get('qr_preset_grid') ?? PresetQr::Superior1->value;
                        $preset = PresetQr::desdeValor((string) $presetValor);
                        if ($preset !== PresetQr::Manual) {
                            $calculador = new CalculadorPosicionQr;
                            $datosQr = new DatosQr(
                                preset: $preset,
                                lado: (float) ($get('qr_lado') ?? 30.0),
                                x: null,
                                y: null,
                                margenX: 5.0,
                                margenY: 5.0,
                                anchoBloqueFactor: 1.2,
                                pagina: 1
                            );

                            $posicion = $calculador->calcular($datosQr, ['width' => $width, 'height' => $height], 1.0, 4.0);

                            $set('qr_x', (int) round($posicion->xQr));
                            $set('qr_y', (int) round($posicion->yQr));
                        }
                    }
                }),
            TextInput::make('qr_lado')
                ->label('Lado del QR (mm)')
                ->numeric()
                ->minValue(10)
                ->required()
                ->live(),
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
            'qr_x' => isset($datosQr['x']) ? (int) round((float) $datosQr['x']) : ($defaults['x'] ?? null),
            'qr_y' => isset($datosQr['y']) ? (int) round((float) $datosQr['y']) : ($defaults['y'] ?? null),
            'qr_pagina' => $record->getAttribute('qr_pagina') ?? ($defaults['pagina'] ?? 1),
        ];
    }
}
