<?php

namespace App\Filament\Resources\Branches;

use App\Filament\Resources\Branches\Pages\ManageBranches;
use App\Models\Branch;
use App\Services\GeocodeService;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Actions as FormActions;
use Filament\Actions\Action as FormAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?string $modelLabel = 'Cabang';

    protected static ?string $pluralModelLabel = 'Cabang';

    protected static ?string $recordTitleAttribute = 'nama_cabang';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_cabang')
                    ->label('Nama Cabang')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(2),

                Textarea::make('alamat')
                    ->label('Alamat')
                    ->required()
                    ->rows(3)
                    ->live(debounce: 1500)
                    ->columnSpanFull(),

                TextInput::make('whatsapp_number')
                    ->label('Nomor WhatsApp Cabang')
                    ->placeholder('628xxxxxxxxxx')
                    ->helperText('Nomor WA ini akan menerima notifikasi booking dari pasien (tipe Klinik).')
                    ->maxLength(20)
                    ->columnSpan(2),

                // ─── Koordinat ───────────────────────────────────────────────
                TextInput::make('lat')
                    ->label('Latitude')
                    ->numeric()
                    ->placeholder('-7.5666')
                    ->live(debounce: 800)
                    ->step(0.00000001),

                TextInput::make('lng')
                    ->label('Longitude')
                    ->numeric()
                    ->placeholder('110.8166')
                    ->live(debounce: 800)
                    ->step(0.00000001),

                // ─── Tombol Geocode ──────────────────────────────────────────
                FormActions::make([
                    FormAction::make('geocodeAddress')
                        ->label('📍 Geocode Alamat Otomatis')
                        ->color('info')
                        ->icon('heroicon-o-map-pin')
                        ->action(function (Get $get, Set $set) {
                            $alamat = $get('alamat');
                            if (blank($alamat)) {
                                Notification::make()
                                    ->title('Alamat kosong')
                                    ->body('Isi kolom Alamat terlebih dahulu.')
                                    ->warning()
                                    ->send();
                                return;
                            }

                            $geocode = app(GeocodeService::class);
                            $coords  = $geocode->geocode($alamat);

                            if ($coords['lat'] && $coords['lng']) {
                                $set('lat', $coords['lat']);
                                $set('lng', $coords['lng']);

                                Notification::make()
                                    ->title('✅ Koordinat ditemukan')
                                    ->body('Lat: ' . $coords['lat'] . ', Lng: ' . $coords['lng'])
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('❌ Gagal menemukan koordinat')
                                    ->body('Coba persingkat alamat (misal: nama kota/kecamatan saja).')
                                    ->danger()
                                    ->send();
                            }
                        }),
                ])->columnSpanFull(),

                // ─── Peta Interaktif ─────────────────────────────────────────
                \Filament\Schemas\Components\View::make('filament.forms.components.branch-map')
                    ->viewData(function (Get $get, $record) {
                        $lat = $get('lat');
                        $lng = $get('lng');
                        // Fallback ke data record jika form state kosong (saat pertama load)
                        if (!$lat && $record) {
                            $lat = $record->lat;
                            $lng = $record->lng;
                        }
                        return [
                            'lat'   => $lat,
                            'lng'   => $lng,
                            'mapId' => 'branch-map-' . ($record?->id ?? 'new'),
                        ];
                    })
                    ->columnSpanFull(),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_cabang')
                    ->label('Nama Cabang')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('alamat')
                    ->label('Alamat')
                    ->limit(60)
                    ->searchable(),
                TextColumn::make('whatsapp_number')
                    ->label('WA Cabang')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('users_count')
                    ->label('Pengguna')
                    ->counts('users')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('nama_cabang');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (! $user || $user->hasRole('super_admin')) {
            return $query;
        }

        return $query->whereKey($user->branch_id);
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageBranches::route('/'),
        ];
    }
}
