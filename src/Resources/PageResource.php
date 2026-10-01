<?php

namespace zaheensayyed\FilamentCms\Resources;

use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use zaheensayyed\FilamentCms\Models\Page;
use zaheensayyed\FilamentCms\Resources\PageResource\Pages;
use zaheensayyed\FilamentCms\Seo\SeoFields;
use zaheensayyed\FilamentCms\Seo\SeoMeta;
use zaheensayyed\FilamentCms\Shield\CmsPermissions;

class PageResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Page::class;

    public static function getPermissionPrefixes(): array
    {
        return CmsPermissions::RESOURCE_PREFIXES;
    }

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make('title')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->live(debounce: 1000)
                            ->afterStateUpdated(function (Set $set, $state) {
                                $set('slug', Str::slug($state));
                            }),
                        TextInput::make('slug')->required(),
                    ])
                    ->columns(2),
                RichEditor::make('body'),
                static::seoSection(),
            ])
            ->columns(1);
    }

    /**
     * Every field is optional; blanks fall back to the SEO Defaults settings (see SeoMeta).
     */
    protected static function seoSection(): Section
    {
        return Section::make('SEO')
            ->description('Optional. Empty fields fall back to the SEO Defaults in Settings.')
            ->collapsed()
            ->schema([
                SeoFields::withCharacterCounter(
                    TextInput::make('meta_title')
                        ->label('Meta title')
                        ->helperText('Recommended up to 60 characters. Falls back to the page title.')
                        ->maxLength(255),
                    60,
                ),
                SeoFields::withCharacterCounter(
                    Textarea::make('meta_description')
                        ->label('Meta description')
                        ->helperText('Recommended 150–160 characters. Falls back to the default description.')
                        ->rows(3)
                        ->maxLength(500),
                    160,
                ),
                Grid::make()
                    ->schema([
                        TextInput::make('canonical_url')
                            ->label('Canonical URL')
                            ->helperText("Falls back to the page's own URL.")
                            ->url()
                            ->maxLength(2048),
                        Select::make('robots')
                            ->options(SeoMeta::ROBOTS_OPTIONS)
                            ->placeholder('Default (from settings)'),
                    ])
                    ->columns(2),
                TextInput::make('og_title')
                    ->label('Social share title')
                    ->helperText('og:title. Falls back to the meta title, then the page title.')
                    ->maxLength(255),
                Textarea::make('og_description')
                    ->label('Social share description')
                    ->helperText('og:description. Falls back to the meta description.')
                    ->rows(3)
                    ->maxLength(500),
                FileUpload::make('og_image')
                    ->label('Social share image')
                    ->helperText('Recommended at least 1200×630 px. Also used as twitter:image. Falls back to the default image.')
                    ->image()
                    ->directory('seo')
                    ->maxSize(2048),
                Textarea::make('structured_data')
                    ->label('Structured data (JSON-LD)')
                    ->helperText('Raw JSON, without the <script> tag.')
                    ->rows(6)
                    ->rule('json'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('title'),
                TextColumn::make('slug'),
                TextColumn::make('createdBy.name')->description(fn (Page $record) => $record->created_at),
                TextColumn::make('updatedBy.name')->description(fn (Page $record) => $record->updated_at),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
