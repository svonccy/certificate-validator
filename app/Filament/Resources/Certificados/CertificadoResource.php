<?php

declare(strict_types=1);

namespace App\Filament\Resources\Certificados;

use App\Filament\Resources\Certificados\Pages\CreateCertificado;
use App\Filament\Resources\Certificados\Pages\EditCertificado;
use App\Filament\Resources\Certificados\Pages\ListCertificados;
use App\Filament\Resources\Certificados\Schemas\CertificadoForm;
use App\Filament\Resources\Certificados\Tables\CertificadosTable;
use App\Models\Certificado;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CertificadoResource extends Resource
{
    protected static ?int $navigationSort = 1;

    protected static ?string $model = Certificado::class;

    protected static ?string $modelLabel = 'Certificado';

    protected static ?string $pluralModelLabel = 'Certificados';

    protected static ?string $navigationLabel = 'Certificados';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return CertificadoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CertificadosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCertificados::route('/'),
            'create' => CreateCertificado::route('/create'),
            'edit' => EditCertificado::route('/{record}/edit'),
        ];
    }
}
