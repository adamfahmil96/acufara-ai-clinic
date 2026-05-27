<?php

namespace App\Filament\Resources\SoapNotes;

use App\Filament\Resources\SoapNotes\Pages\CreateSoapNote;
use App\Filament\Resources\SoapNotes\Pages\EditSoapNote;
use App\Filament\Resources\SoapNotes\Pages\ListSoapNotes;
use App\Filament\Resources\SoapNotes\Schemas\SoapNoteForm;
use App\Filament\Resources\SoapNotes\Tables\SoapNotesTable;
use App\Models\SoapNote;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SoapNoteResource extends Resource
{
    protected static ?string $model = SoapNote::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return SoapNoteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SoapNotesTable::configure($table);
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
            'index' => ListSoapNotes::route('/'),
            'create' => CreateSoapNote::route('/create'),
            'edit' => EditSoapNote::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
