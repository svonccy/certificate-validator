<?php

declare(strict_types=1);

namespace App\Filament\Resources\Certificados;

use App\Filament\Resources\Certificados\Pages\CreateCertificado;
use App\Filament\Resources\Certificados\Pages\EditCertificado;
use App\Filament\Resources\Certificados\Pages\ListCertificados;
use App\Filament\Resources\Certificados\Pages\ViewCertificado;
use App\Filament\Resources\Certificados\Schemas\CertificadoForm;
use App\Filament\Resources\Certificados\Schemas\CertificadoInfolist;
use App\Filament\Resources\Certificados\Tables\CertificadosTable;
use App\Models\Certificado;
use BackedEnum;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CertificadoResource extends Resource
{
    protected static ?int $navigationSort = 0;

    protected static ?string $slug = 'documentos/certificados';

    protected static ?string $model = Certificado::class;

    protected static ?string $modelLabel = 'Certificado';

    protected static ?string $pluralModelLabel = 'Certificados';

    protected static ?string $navigationLabel = 'Certificados';

    protected static string|UnitEnum|null $navigationGroup = 'Documentos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Schema $schema): Schema
    {
        return CertificadoForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CertificadoInfolist::configure($schema);
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

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            ViewCertificado::class,
            EditCertificado::class,
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCertificados::route('/'),
            'create' => CreateCertificado::route('/create'),
            'view' => ViewCertificado::route('/{record}'),
            'edit' => EditCertificado::route('/{record}/edit'),
        ];
    }
}
