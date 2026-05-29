<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

use BackedEnum;
use UnitEnum;

class DeveloperInfo extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-information-circle';
    protected static ?string $navigationLabel = 'Informasi Developer';
    protected static ?string $title = 'Informasi Developer';
    protected static ?string $slug = 'developer-info';
    protected static string|UnitEnum|null $navigationGroup = 'Akses';
    protected static ?int $navigationSort = 101;

    protected string $view = 'filament.pages.developer-info';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'developer', 'demo_super_admin', 'branch_admin']) ?? false;
    }
}
