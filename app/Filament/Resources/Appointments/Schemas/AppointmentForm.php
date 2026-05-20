<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Models\Appointment;
use App\Services\GeminiService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Actions;
use Filament\Actions\Action as FormAction;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

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
                                    ->getSearchResultsUsing(fn (string $search): array => \App\Models\Patient::whereHas('user', fn ($query) => $query->where('name', 'ilike', "%{$search}%"))->limit(50)->get()->mapWithKeys(fn ($patient) => [$patient->id => $patient->user?->name])->toArray())
                                    ->getOptionLabelUsing(fn ($value): ?string => \App\Models\Patient::find($value)?->user?->name)
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

                                // ─── Tombol Analisis AI ─────────────────────────────
                                Actions::make([
                                    FormAction::make('analyzeComplaint')
                                        ->label('🔍 Analisis Keluhan dengan AI')
                                        ->color('success')
                                        ->requiresConfirmation()
                                        ->modalHeading('Analisis Keluhan dengan Gemini AI')
                                        ->modalDescription('AI akan menganalisis keluhan pasien dan memberikan rekomendasi urgensi serta rute kunjungan. Lanjutkan?')
                                        ->modalSubmitActionLabel('Ya, Analisis Sekarang')
                                        ->action(function (Get $get, Set $set) {
                                            $complaint = $get('complaint_summary');

                                            if (blank($complaint)) {
                                                Notification::make()
                                                    ->title('Keluhan kosong')
                                                    ->body('Silakan isi ringkasan keluhan terlebih dahulu.')
                                                    ->warning()
                                                    ->send();
                                                return;
                                            }

                                            try {
                                                /** @var GeminiService $gemini */
                                                $gemini = app(GeminiService::class);
                                                $result = $gemini->analyzeComplaint($complaint);

                                                $set('ai_urgency',        $result['urgency']        ?? '');
                                                $set('ai_recommendation', $result['recommendation'] ?? '');
                                                $set('ai_notes',          $result['notes']          ?? '');

                                                Notification::make()
                                                    ->title('✅ Analisis selesai')
                                                    ->body('Hasil analisis AI telah diisi. Simpan appointment untuk menyimpan hasilnya.')
                                                    ->success()
                                                    ->send();
                                            } catch (\Throwable $e) {
                                                Notification::make()
                                                    ->title('❌ Gagal menghubungi AI')
                                                    ->body('Error: ' . $e->getMessage())
                                                    ->danger()
                                                    ->send();
                                            }
                                        }),
                                ])->columnSpanFull(),
                            ]),

                        // ─── Hasil Analisis AI ───────────────────────────────────
                        Section::make('🤖 Hasil Analisis AI (Triage)')
                            ->description('Diisi otomatis oleh Gemini AI saat tombol "Analisis Keluhan" ditekan.')
                            ->schema([
                                Select::make('ai_urgency')
                                    ->label('Tingkat Urgensi')
                                    ->options([
                                        'rendah' => '🟢 Rendah',
                                        'sedang' => '🟡 Sedang',
                                        'tinggi' => '🔴 Tinggi',
                                    ])
                                    ->native(false),
                                Textarea::make('ai_recommendation')
                                    ->label('Rekomendasi Kunjungan')
                                    ->rows(3)
                                    ->columnSpanFull(),
                                Textarea::make('ai_notes')
                                    ->label('Catatan Tambahan untuk Terapis')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->collapsed(),
                    ])
                    ->columnSpan(['lg' => 2]),

                Group::make()
                    ->schema([
                        Section::make('Status & Lokasi')
                            ->schema([
                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        Appointment::STATUS_SCHEDULED   => 'Terjadwal',
                                        Appointment::STATUS_IN_PROGRESS => 'Sedang Berlangsung',
                                        Appointment::STATUS_COMPLETED   => 'Selesai',
                                        Appointment::STATUS_CANCELLED   => 'Dibatalkan',
                                    ])
                                    ->required()
                                    ->default(Appointment::STATUS_SCHEDULED),

                                Select::make('service_location_type')
                                    ->label('Tipe Lokasi')
                                    ->options([
                                        Appointment::LOCATION_CLINIC   => 'Klinik',
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
