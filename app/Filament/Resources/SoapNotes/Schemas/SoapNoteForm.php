<?php

namespace App\Filament\Resources\SoapNotes\Schemas;

use App\Services\GeminiService;
use Filament\Forms\Components\KeyValue;
use Filament\Actions\Action as FormAction;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;

class SoapNoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('appointment_id')
                    ->label('Kunjungan (Pasien & Tanggal)')
                    ->getSearchResultsUsing(fn (string $search): array => \App\Models\Appointment::with(['patient.user'])->whereHas('patient.user', fn ($query) => $query->where('name', 'ilike', "%{$search}%"))->limit(50)->get()->mapWithKeys(fn ($record) => [$record->id => "{$record->patient?->user?->name} - {$record->scheduled_at->format('d M Y H:i')}"])->toArray())
                    ->getOptionLabelUsing(fn ($value): ?string => (($record = \App\Models\Appointment::with('patient.user')->find($value)) ? "{$record->patient?->user?->name} - {$record->scheduled_at->format('d M Y H:i')}" : null))
                    ->required()
                    ->searchable()
                    ->columnSpanFull(),

                // ─── AcuVoice Section ─────────────────────────────────────────────
                Section::make('🎙️ AcuVoice — Asisten AI')
                    ->description('Rekam percakapan atau ketik langsung. Klik "✨ Format dengan AI" untuk mengisi field SOAP secara otomatis.')
                    ->schema([
                        // Tombol rekam suara (client-side Alpine.js)
                        View::make('filament.components.acuvoice-recorder')
                            ->columnSpanFull(),

                        Textarea::make('raw_transcript')
                            ->label('Transkrip Kasar (Raw Transcript)')
                            ->helperText('Diisi otomatis saat merekam, atau ketik di sini.')
                            ->rows(5)
                            ->live(debounce: 800)
                            ->columnSpanFull(),

                        // Tombol Format dengan AI (server action)
                        Actions::make([
                            FormAction::make('formatWithAi')
                                ->label('✨ Format dengan AI')
                                ->color('success')
                                ->icon('heroicon-o-sparkles')
                                ->requiresConfirmation()
                                ->modalHeading('Format SOAP dengan Gemini AI')
                                ->modalDescription('AI akan menganalisis transkrip dan mengisi field Subjektif, Objektif, Assessment, dan Plan secara otomatis. Lanjutkan?')
                                ->modalSubmitActionLabel('Ya, Format Sekarang')
                                ->action(function (Set $set, $get) {
                                    $rawTranscript = $get('raw_transcript');

                                    if (blank($rawTranscript)) {
                                        Notification::make()
                                            ->title('Transkrip kosong')
                                            ->body('Silakan rekam suara atau ketik transkrip terlebih dahulu.')
                                            ->warning()
                                            ->send();
                                        return;
                                    }

                                    try {
                                        /** @var \App\Services\GeminiService $gemini */
                                        $gemini = app(GeminiService::class);
                                        $result = $gemini->formatSoapNote($rawTranscript);

                                        $set('subjective', $result['subjective'] ?? '');
                                        $set('objective',  $result['objective']  ?? '');
                                        $set('assessment', $result['assessment'] ?? '');
                                        $set('plan',       $result['plan']       ?? '');

                                        Notification::make()
                                            ->title('✅ Berhasil diformat oleh AI')
                                            ->body('Field SOAP telah diisi otomatis. Periksa dan sesuaikan jika perlu.')
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
                    ])
                    ->collapsed(false)
                    ->columnSpanFull(),

                // ─── Anamnesa Section ─────────────────────────────────────────────
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
                    ])->columnSpanFull(),

                // ─── Terapi Section ───────────────────────────────────────────────
                Section::make('Terapi (Assessment & Plan)')
                    ->schema([
                        Textarea::make('assessment')
                            ->label('Assessment (Diagnosa Kerja)')
                            ->rows(6)
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
                    ])->columnSpanFull(),
            ])->columns(1);
    }
}
