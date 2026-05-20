<?php

namespace App\Filament\Pages;

use App\Models\Appointment;
use App\Services\GeminiService;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use UnitEnum;

class HomecareRouting extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;
    protected static string|UnitEnum|null $navigationGroup = 'Manajemen Jadwal';
    protected static ?string $navigationLabel = 'Homecare Routing';
    protected static ?string $title = 'Smart Homecare Routing';
    protected static ?string $slug = 'homecare-routing';
    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.homecare-routing';

    public string $selectedDate;
    public ?string $aiSuggestion = null;

    public function mount(): void
    {
        $this->selectedDate = now()->toDateString();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Base query: hanya filter tipe homecare.
                // Filter tanggal dikontrol sepenuhnya oleh panel filter di bawah.
                Appointment::query()
                    ->with(['patient.user', 'service'])
                    ->where('service_location_type', Appointment::LOCATION_HOMECARE)
            )
            ->columns([
                TextColumn::make('scheduled_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                TextColumn::make('patient.user.name')
                    ->label('Pasien')
                    ->searchable(),
                TextColumn::make('patient.user.whatsapp_number')
                    ->label('WhatsApp'),
                TextColumn::make('service.name')
                    ->label('Layanan')
                    ->badge()
                    ->color('info'),
                TextColumn::make('address_at_time')
                    ->label('Alamat Kunjungan (Teks Asli)')
                    ->wrap()
                    ->words(30),
            ])
            ->filters([
                Filter::make('tanggal_kunjungan')
                    ->label('Tanggal Kunjungan')
                    ->form([
                        DatePicker::make('date')
                            ->label('Pilih Tanggal')
                            ->default(now()->toDateString()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $date = $data['date'] ?? now()->toDateString();
                        $this->selectedDate = $date;
                        return $query->whereDate('scheduled_at', $date);
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (empty($data['date'])) {
                            return null;
                        }
                        return 'Tanggal: ' . Carbon::parse($data['date'])->translatedFormat('d F Y');
                    }),
            ])
            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->defaultSort('scheduled_at', 'asc')
            ->emptyStateHeading('Tidak Ada Jadwal Homecare')
            ->emptyStateDescription('Tidak ada pasien yang memesan layanan homecare untuk tanggal yang dipilih.')
            ->emptyStateIcon('heroicon-o-inbox');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('optimizeRoute')
                ->label('Optimasi Rute dengan AI')
                ->icon(Heroicon::OutlinedSparkles)
                ->color('success')
                ->action(function (GeminiService $gemini) {
                    $appointments = Appointment::query()
                        ->with(['patient.user', 'service'])
                        ->where('service_location_type', Appointment::LOCATION_HOMECARE)
                        ->whereDate('scheduled_at', $this->selectedDate)
                        ->orderBy('scheduled_at')
                        ->get();

                    if ($appointments->isEmpty()) {
                        Notification::make()
                            ->title('Tidak ada jadwal homecare untuk tanggal yang dipilih.')
                            ->warning()
                            ->send();
                        return;
                    }

                    try {
                        $branch = \App\Models\Branch::first();
                        $branchAddress = $branch
                            ? $branch->nama_cabang . ', ' . $branch->alamat
                            : 'Klinik Acufara';

                        $this->aiSuggestion = $gemini->optimizeRoute($appointments, $branchAddress);

                        Notification::make()
                            ->title('Rute berhasil dioptimasi oleh AI!')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Log::error('HomecareRouting optimizeRoute: ' . $e->getMessage());
                        Notification::make()
                            ->title('Gagal menghubungi AI')
                            ->body('Silakan coba lagi atau periksa koneksi.')
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
