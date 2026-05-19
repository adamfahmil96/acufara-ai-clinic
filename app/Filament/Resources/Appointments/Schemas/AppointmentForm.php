<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Models\Appointment;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class AppointmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make('Informasi Kunjungan')
                            ->schema([
                                Select::make('branch_id')
                                    ->label('Cabang')
                                    ->relationship('branch', 'nama_cabang')
                                    ->required()
                                    ->searchable(),
                                Select::make('patient_id')
                                    ->label('Pasien')
                                    ->relationship('patient.user', 'name')
                                    ->required()
                                    ->searchable(),
                                Select::make('service_id')
                                    ->label('Layanan')
                                    ->relationship('service', 'name')
                                    ->required()
                                    ->searchable(),
                                DateTimePicker::make('scheduled_at')
                                    ->label('Jadwal Kunjungan')
                                    ->required(),
                                TextInput::make('final_price')
                                    ->label('Harga Akhir (Rp)')
                                    ->numeric()
                                    ->prefix('Rp'),
                            ])
                            ->columns(2),

                        Section::make('Keluhan Pasien')
                            ->schema([
                                Textarea::make('complaint_summary')
                                    ->label('Ringkasan Keluhan')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Group::make()
                    ->schema([
                        Section::make('Status & Lokasi')
                            ->schema([
                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        Appointment::STATUS_SCHEDULED => 'Terjadwal',
                                        Appointment::STATUS_IN_PROGRESS => 'Sedang Berlangsung',
                                        Appointment::STATUS_COMPLETED => 'Selesai',
                                        Appointment::STATUS_CANCELLED => 'Dibatalkan',
                                    ])
                                    ->required()
                                    ->default(Appointment::STATUS_SCHEDULED),

                                Select::make('service_location_type')
                                    ->label('Tipe Lokasi')
                                    ->options([
                                        Appointment::LOCATION_CLINIC => 'Klinik',
                                        Appointment::LOCATION_HOMECARE => 'Homecare',
                                    ])
                                    ->required()
                                    ->live()
                                    ->default(Appointment::LOCATION_CLINIC),
                            ]),

                        Section::make('Detail Homecare')
                            ->schema([
                                Textarea::make('address_at_time')
                                    ->label('Alamat Kunjungan')
                                    ->rows(2)
                                    ->columnSpanFull(),
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('lat')
                                            ->label('Latitude')
                                            ->numeric(),
                                        TextInput::make('lng')
                                            ->label('Longitude')
                                            ->numeric(),
                                    ]),
                            ])
                            ->visible(fn (Get $get): bool => $get('service_location_type') === Appointment::LOCATION_HOMECARE)
                            ->collapsed(false),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }
}
