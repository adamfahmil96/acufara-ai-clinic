<?php

namespace App\Filament\Resources\SoapNotes\Pages;

use App\Filament\Resources\SoapNotes\SoapNoteResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditSoapNote extends EditRecord
{
    protected static string $resource = SoapNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
