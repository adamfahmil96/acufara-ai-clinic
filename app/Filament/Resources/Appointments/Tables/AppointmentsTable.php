<?php

namespace App\Filament\Resources\Appointments\Tables;

use App\Models\Appointment;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class AppointmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('scheduled_at')
                    ->label('Jadwal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('patient.user.name')
                    ->label('Pasien')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('service.name')
                    ->label('Layanan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('branch.nama_cabang')
                    ->label('Cabang')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('service_location_type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Appointment::LOCATION_CLINIC => 'success',
                        Appointment::LOCATION_HOMECARE => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Appointment::STATUS_SCHEDULED => 'gray',
                        Appointment::STATUS_IN_PROGRESS => 'warning',
                        Appointment::STATUS_COMPLETED => 'success',
                        Appointment::STATUS_CANCELLED => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Appointment::STATUS_SCHEDULED => 'Terjadwal',
                        Appointment::STATUS_IN_PROGRESS => 'Sedang Berlangsung',
                        Appointment::STATUS_COMPLETED => 'Selesai',
                        Appointment::STATUS_CANCELLED => 'Dibatalkan',
                        default => $state,
                    }),
                TextColumn::make('final_price')
                    ->label('Harga')
                    ->formatStateUsing(fn ($state): string => auth()->user()->isDemo() ? 'Rp ***' : 'Rp '.number_format($state ?? 0, 0, ',', '.'))
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('start')
                    ->label('Mulai')
                    ->icon('heroicon-o-play')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(fn (Appointment $record) => $record->update(['status' => Appointment::STATUS_IN_PROGRESS]))
                    ->visible(fn (Appointment $record) => $record->status === Appointment::STATUS_SCHEDULED),
                Action::make('complete')
                    ->label('Selesai')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Appointment $record) => $record->update(['status' => Appointment::STATUS_COMPLETED]))
                    ->visible(fn (Appointment $record) => $record->status === Appointment::STATUS_IN_PROGRESS),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('scheduled_at', 'desc');
    }
}
