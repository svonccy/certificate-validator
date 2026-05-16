<?php

declare(strict_types=1);

namespace App\Filament\Resources\FirmasConfianza;

use App\Filament\Resources\FirmasConfianza\Pages\CreateFirmaConfianza;
use App\Filament\Resources\FirmasConfianza\Pages\EditFirmaConfianza;
use App\Filament\Resources\FirmasConfianza\Pages\ListFirmasConfianza;
use App\Filament\Resources\FirmasConfianza\Schemas\FirmaConfianzaForm;
use App\Filament\Resources\FirmasConfianza\Tables\FirmaConfianzasTable;
use App\Models\FirmaConfianza;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FirmaConfianzaResource extends Resource
{
    protected static ?int $navigationSort = 2;

    protected static ?string $model = FirmaConfianza::class;

    protected static ?string $modelLabel = 'Firma autorizada';

    protected static ?string $pluralModelLabel = 'Firmas autorizadas';

    protected static ?string $navigationLabel = 'Firmas autorizadas';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    public static function form(Schema $schema): Schema
    {
        return FirmaConfianzaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FirmaConfianzasTable::configure($table);
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
            'index' => ListFirmasConfianza::route('/'),
            'create' => CreateFirmaConfianza::route('/create'),
            'edit' => EditFirmaConfianza::route('/{record}/edit'),
        ];
    }
}
