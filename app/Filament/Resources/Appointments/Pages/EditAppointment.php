<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Filament\Resources\Appointments\AppointmentResource;
use App\Models\Appointment;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditAppointment extends EditRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('start')
                ->label('Mulai Layanan')
                ->icon('heroicon-o-play')
                ->color('warning')
                ->requiresConfirmation()
                ->action(function (Appointment $record) {
                    $record->update(['status' => Appointment::STATUS_IN_PROGRESS]);
                    $this->refreshFormData(['status']);
                })
                ->visible(fn (Appointment $record) => $record->status === Appointment::STATUS_SCHEDULED),
            
            Action::make('complete')
                ->label('Selesaikan Layanan')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->action(function (Appointment $record) {
                    $record->update(['status' => Appointment::STATUS_COMPLETED]);
                    $this->refreshFormData(['status']);
                })
                ->visible(fn (Appointment $record) => $record->status === Appointment::STATUS_IN_PROGRESS),

            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
