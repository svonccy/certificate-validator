<?php

declare(strict_types=1);

namespace App\Filament\Resources\CertificadoConfianzas;

use App\Filament\Resources\CertificadoConfianzas\Pages\CreateCertificadoConfianza;
use App\Filament\Resources\CertificadoConfianzas\Pages\EditCertificadoConfianza;
use App\Filament\Resources\CertificadoConfianzas\Pages\ListCertificadoConfianzas;
use App\Filament\Resources\CertificadoConfianzas\Schemas\CertificadoConfianzaForm;
use App\Filament\Resources\CertificadoConfianzas\Tables\CertificadoConfianzasTable;
use App\Models\CertificadoConfianza;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CertificadoConfianzaResource extends Resource
{
    protected static ?int $navigationSort = 2;

    protected static ?string $model = CertificadoConfianza::class;

    protected static ?string $modelLabel = 'Firma autorizada';

    protected static ?string $pluralModelLabel = 'Firmas autorizadas';

    protected static ?string $navigationLabel = 'Firmas autorizadas';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    public static function form(Schema $schema): Schema
    {
        return CertificadoConfianzaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CertificadoConfianzasTable::configure($table);
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
            'index' => ListCertificadoConfianzas::route('/'),
            'create' => CreateCertificadoConfianza::route('/create'),
            'edit' => EditCertificadoConfianza::route('/{record}/edit'),
        ];
    }
}
