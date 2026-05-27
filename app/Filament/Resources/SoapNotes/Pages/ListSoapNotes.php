<?php

namespace App\Filament\Resources\SoapNotes\Pages;

use App\Filament\Resources\SoapNotes\SoapNoteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSoapNotes extends ListRecords
{
    protected static string $resource = SoapNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
