<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use App\Models\Branch;

class Dashboard extends \Filament\Pages\Dashboard
{
    use HasFiltersForm;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('branch_id')
                    ->label('Filter Cabang')
                    ->options(Branch::pluck('nama_cabang', 'id'))
                    ->searchable()
                    ->placeholder('Semua Cabang')
                    ->visible(fn () => auth()->user()->hasRole('super_admin')),
            ])
            ->columns(3);
    }
}
