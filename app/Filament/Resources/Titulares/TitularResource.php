<?php

declare(strict_types=1);

namespace App\Filament\Resources\Titulares;

use App\Filament\Resources\Titulares\Pages\CreateTitular;
use App\Filament\Resources\Titulares\Pages\EditTitular;
use App\Filament\Resources\Titulares\Pages\ListTitulares;
use App\Filament\Resources\Titulares\Pages\ViewTitular;
use App\Filament\Resources\Titulares\Schemas\TitularForm;
use App\Filament\Resources\Titulares\Schemas\TitularInfolist;
use App\Filament\Resources\Titulares\Tables\TitularesTable;
use App\Models\Titular;
use BackedEnum;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TitularResource extends Resource
{
    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'documentos/titulares';

    protected static ?string $model = Titular::class;

    protected static ?string $modelLabel = 'Titular';

    protected static ?string $pluralModelLabel = 'Titulares';

    protected static ?string $navigationLabel = 'Titulares';

    protected static string|UnitEnum|null $navigationGroup = 'Documentos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Schema $schema): Schema
    {
        return TitularForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TitularInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TitularesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            ViewTitular::class,
            EditTitular::class,
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTitulares::route('/'),
            'create' => CreateTitular::route('/create'),
            'view' => ViewTitular::route('/{record}'),
            'edit' => EditTitular::route('/{record}/edit'),
        ];
    }
}
