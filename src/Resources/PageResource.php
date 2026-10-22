<?php

namespace zaheensayyed\FilamentCms\Resources;

use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
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
use zaheensayyed\FilamentCms\Shield\CmsPermissions;
use zaheensayyed\FilamentCms\Support\Heroicons;

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
                Grid::make()
                    ->schema([
                        Textarea::make('description')
                            ->helperText('Short intro shown under the page title. Also the meta description when that is empty.')
                            ->rows(3)
                            ->maxLength(500),
                        Heroicons::select('icon')
                            ->helperText('Shown before the page content. Type to search, e.g. "fire", "camera" or "shield solid".'),
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
        return SeoFields::section();
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
