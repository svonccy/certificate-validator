<?php

declare(strict_types=1);

namespace App\Filament\Resources\Certificados\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
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
                                        DatePicker::make('fecha_emision')
                                            ->label('Fecha de Emisión')
                                            ->required()
                                            ->default(now()),
                                        ToggleButtons::make('estado')
                                            ->label('Estado')
                                            ->options([
                                                'PENDIENTE' => 'Pendiente',
                                                'FIRMADO' => 'Firmado',
                                            ])
                                            ->colors([
                                                'PENDIENTE' => 'warning',
                                                'FIRMADO' => 'success',
                                            ])
                                            ->inline()
                                            ->grouped(),
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
                                                fn (): \Closure => function (string $attribute, mixed $value, \Closure $fail): void {
                                                    if (is_object($value)) {
                                                        $exists = false;
                                                        if (method_exists($value, 'exists')) {
                                                            $exists = $value->exists();
                                                        } elseif (method_exists($value, 'getRealPath')) {
                                                            $path = $value->getRealPath();
                                                            $exists = ! empty($path) && file_exists($path);
                                                        }

                                                        if ($exists) {
                                                            try {
                                                                $content = method_exists($value, 'get') ? $value->get() : file_get_contents($value->getRealPath());
                                                                if (str_contains($content, 'CNSM-TOKEN:')) {
                                                                    $fail('El archivo seleccionado es un borrador que ya contiene un código QR. Por favor, sube la plantilla original limpia.');
                                                                }
                                                            } catch (\Throwable $e) {
                                                                //
                                                            }
                                                        }
                                                    }
                                                },
                                            ]),
                                    ]),
                            ])
                            ->columnSpan(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
