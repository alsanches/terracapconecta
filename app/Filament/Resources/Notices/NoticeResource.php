<?php

namespace App\Filament\Resources\Notices;

use App\Filament\Resources\Notices\Pages\CreateNotice;
use App\Filament\Resources\Notices\Pages\EditNotice;
use App\Filament\Resources\Notices\Pages\ListNotices;
use App\Filament\Resources\Notices\Pages\ViewNotice;
use App\Filament\Resources\Notices\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\Notices\Schemas\NoticeForm;
use App\Filament\Resources\Notices\Schemas\NoticeInfolist;
use App\Filament\Resources\Notices\Tables\NoticesTable;
use App\Models\Notice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\ValidationException;

class NoticeResource extends Resource
{
    protected static ?string $model = Notice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Editais';

    protected static ?string $modelLabel = 'edital';

    protected static ?string $pluralModelLabel = 'editais';

    protected static ?string $recordTitleAttribute = 'code';

    public static function form(Schema $schema): Schema
    {
        return NoticeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return NoticeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NoticesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNotices::route('/'),
            'create' => CreateNotice::route('/create'),
            'view' => ViewNotice::route('/{record}'),
            'edit' => EditNotice::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function validatePublication(array $data): array
    {
        $errors = [];

        if (($data['public_visible'] ?? false) && ! ($data['is_demo'] ?? true)) {
            if (blank($data['official_page_url'] ?? null)) {
                $errors['data.official_page_url'] = 'Um edital oficial publicado exige a página oficial da fonte.';
            }
            if (blank($data['source_checked_at'] ?? null)) {
                $errors['data.source_checked_at'] = 'Informe quando a fonte oficial foi conferida.';
            }
        }

        if (($data['status'] ?? null) === 'open' && blank($data['proposal_deadline'] ?? null) && blank($data['closes_at'] ?? null) && blank($data['auction_at'] ?? null)) {
            $errors['data.proposal_deadline'] = 'Um edital aberto exige ao menos um prazo principal.';
        }

        foreach (['document_url', 'official_page_url', 'proposal_url', 'result_url'] as $field) {
            if (filled($data[$field] ?? null) && ! str_starts_with($data[$field], 'https://')) {
                $errors["data.{$field}"] = 'Use um endereço externo HTTPS.';
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        return $data;
    }
}
