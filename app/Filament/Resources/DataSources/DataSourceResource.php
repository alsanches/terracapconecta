<?php

namespace App\Filament\Resources\DataSources;

use App\Filament\Resources\DataSources\Pages\CreateDataSource;
use App\Filament\Resources\DataSources\Pages\EditDataSource;
use App\Filament\Resources\DataSources\Pages\ListDataSources;
use App\Filament\Resources\DataSources\Pages\ViewDataSource;
use App\Filament\Resources\DataSources\Schemas\DataSourceForm;
use App\Filament\Resources\DataSources\Schemas\DataSourceInfolist;
use App\Filament\Resources\DataSources\Tables\DataSourcesTable;
use App\Models\DataSource;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DataSourceResource extends Resource
{
    protected static ?string $model = DataSource::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Fontes de dados';

    protected static ?string $modelLabel = 'fonte de dados';

    protected static ?string $pluralModelLabel = 'fontes de dados';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return DataSourceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DataSourceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DataSourcesTable::configure($table);
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
            'index' => ListDataSources::route('/'),
            'create' => CreateDataSource::route('/create'),
            'view' => ViewDataSource::route('/{record}'),
            'edit' => EditDataSource::route('/{record}/edit'),
        ];
    }
}
