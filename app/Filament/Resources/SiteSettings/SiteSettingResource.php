<?php

namespace App\Filament\Resources\SiteSettings;

use App\Filament\Resources\SiteSettings\Pages\ManageSiteSettings;
use App\Models\SiteSetting;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class SiteSettingResource extends Resource
{
    protected static ?string $model = SiteSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Konten';

    protected static ?string $modelLabel = 'Pengaturan Situs';

    protected static ?string $pluralModelLabel = 'Pengaturan Situs';

    protected static ?string $recordTitleAttribute = 'setting_key';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('setting_key')
                    ->label('Key')
                    ->options(self::settingKeyOptions())
                    ->searchable()
                    ->required()
                    ->unique(ignoreRecord: true),
                Textarea::make('setting_value')
                    ->label('Value')
                    ->required()
                    ->rows(5)
                    ->columnSpanFull(),
                Tabs::make('Referensi Key')
                    ->tabs([
                        Tab::make('Header')
                            ->schema([
                                Placeholder::make('header_keys')
                                    ->hiddenLabel()
                                    ->content('header.brand_name, header.whatsapp_number'),
                            ]),
                        Tab::make('Konten')
                            ->schema([
                                Placeholder::make('content_keys')
                                    ->hiddenLabel()
                                    ->content('hero.title, hero.subtitle, hero.cta_label, content.about_title, content.about_body'),
                            ]),
                        Tab::make('Footer')
                            ->schema([
                                Placeholder::make('footer_keys')
                                    ->hiddenLabel()
                                    ->content('footer.address, footer.instagram, footer.whatsapp, footer.tiktok'),
                            ]),
                        Tab::make('SEO')
                            ->schema([
                                Placeholder::make('seo_keys')
                                    ->hiddenLabel()
                                    ->content('seo.meta_title, seo.meta_description'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('setting_key')
                    ->label('Key')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('setting_value')
                    ->label('Value')
                    ->limit(80)
                    ->searchable(),
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('group')
                    ->label('Grup')
                    ->options([
                        'header' => 'Header',
                        'hero' => 'Hero',
                        'content' => 'Konten',
                        'footer' => 'Footer',
                        'seo' => 'SEO',
                    ])
                    ->query(fn ($query, array $data) => filled($data['value'] ?? null)
                        ? $query->where('setting_key', 'like', $data['value'] . '.%')
                        : $query),
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
            ->defaultSort('setting_key');
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
            'index' => ManageSiteSettings::route('/'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function settingKeyOptions(): array
    {
        return [
            'header.brand_name' => 'Header: Brand Name',
            'header.whatsapp_number' => 'Header: WhatsApp',
            'hero.title' => 'Konten: Hero Title',
            'hero.subtitle' => 'Konten: Hero Subtitle',
            'hero.cta_label' => 'Konten: CTA Label',
            'content.about_title' => 'Konten: About Title',
            'content.about_body' => 'Konten: About Body',
            'footer.address' => 'Footer: Address',
            'footer.instagram' => 'Footer: Instagram URL',
            'footer.whatsapp' => 'Footer: WhatsApp URL',
            'footer.tiktok' => 'Footer: TikTok URL',
            'seo.meta_title' => 'SEO: Meta Title',
            'seo.meta_description' => 'SEO: Meta Description',
        ];
    }
}
