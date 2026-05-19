<?php

namespace App\Filament\Resources\SoapNotes\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SoapNoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('appointment_id')
                    ->label('Kunjungan (Pasien & Tanggal)')
                    ->relationship('appointment', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->patient->user->name} - {$record->scheduled_at->format('d M Y H:i')}")
                    ->required()
                    ->searchable()
                    ->columnSpanFull(),

                Section::make('AcuVoice (Asisten AI)')
                    ->description('Rekam percakapan atau ketik langsung di sini untuk dianalisis oleh AI nanti.')
                    ->schema([
                        Textarea::make('raw_transcript')
                            ->label('Transkrip Kasar (Raw Transcript)')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->collapsed(false),

                Section::make('Anamnesa (Subjektif & Objektif)')
                    ->schema([
                        Textarea::make('subjective')
                            ->label('Subjektif (Keluhan Utama)')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('objective')
                            ->label('Objektif (Pemeriksaan Fisik)')
                            ->rows(3)
                            ->columnSpanFull(),
                        SpatieMediaLibraryFileUpload::make('anamnesa_images')
                            ->label('Foto Kondisi Awal Pasien')
                            ->collection('anamnesa')
                            ->multiple()
                            ->conversion('thumb')
                            ->columnSpanFull(),
                    ]),

                Section::make('Terapi (Assessment & Plan)')
                    ->schema([
                        Textarea::make('assessment')
                            ->label('Assessment (Diagnosa Kerja)')
                            ->rows(2)
                            ->columnSpanFull(),
                        KeyValue::make('treatment_details')
                            ->label('Detail Tindakan (Treatment Details)')
                            ->keyLabel('Jenis / Area')
                            ->valueLabel('Keterangan / Titik')
                            ->columnSpanFull(),
                        Textarea::make('plan')
                            ->label('Plan (Rencana Tindak Lanjut)')
                            ->rows(3)
                            ->columnSpanFull(),
                        SpatieMediaLibraryFileUpload::make('therapy_images')
                            ->label('Foto Pasca Tindakan')
                            ->collection('therapy')
                            ->multiple()
                            ->conversion('thumb')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
