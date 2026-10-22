<?php

namespace zaheensayyed\FilamentCms\Resources;

use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use zaheensayyed\FilamentCms\Models\Client;
use zaheensayyed\FilamentCms\Resources\ClientResource\Pages;
use zaheensayyed\FilamentCms\Seo\SeoFields;
use zaheensayyed\FilamentCms\Shield\CmsPermissions;

class ClientResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getPermissionPrefixes(): array
    {
        return CmsPermissions::RESOURCE_PREFIXES;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                // The slug is generated from the title (Client::uniqueSlug()); clients have no pages of their own.
                                TextInput::make('name')
                                    ->label('Title')
                                    ->required()
                                    ->maxLength(255),
                                Textarea::make('description')
                                    ->helperText('A short description for the card on the clients page.')
                                    ->rows(3)
                                    ->maxLength(500),
                                FileUpload::make('logo')
                                    ->helperText('One image, shown on the card.')
                                    ->image()
                                    // Whole logo in a fixed-height preview instead of a full-width crop.
                                    ->imagePreviewHeight('160')
                                    ->directory('clients/logos')
                                    ->maxSize(2048),
                            ])
                            ->columnSpan(['lg' => 2]),
                        Section::make('Visibility')
                            ->schema([
                                Toggle::make('is_featured')
                                    ->label('Featured')
                                    ->helperText('Show this client on the home page.'),
                                Toggle::make('is_published')
                                    ->label('Published')
                                    ->helperText('Off hides the client from the clients page and the home page.')
                                    ->default(true),
                            ])
                            ->columnSpan(['lg' => 1]),
                    ])
                    ->columns(['lg' => 3]),
                Section::make('More details')
                    ->description('Optional. Stored for later; the website shows the logo, title and description.')
                    ->collapsed()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('industry')->maxLength(255),
                                TextInput::make('location')->maxLength(255),
                                TextInput::make('website_url')->label('Website')->url()->maxLength(2048),
                            ]),
                        RichEditor::make('body')
                            ->label('Project details')
                            ->fileAttachmentsDirectory('clients'),
                    ]),
                SeoFields::section('client'),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('logo')
                    ->label('Logo')
                    ->state(fn (Client $record) => $record->logo_url)
                    ->height(48)
                    ->width(72)
                    ->extraImgAttributes(['class' => 'object-contain rounded bg-gray-50 dark:bg-white/5']),
                TextColumn::make('name')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Client $record) => Str::limit((string) $record->description, 80)),
                ToggleColumn::make('is_featured')
                    ->label('Featured')
                    ->disabled(fn (Client $record) => ! static::canEdit($record)),
                IconColumn::make('is_published')->label('Published')->boolean(),
                TextColumn::make('updatedBy.name')
                    ->description(fn (Client $record) => $record->updated_at)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_featured')->label('Featured'),
                TernaryFilter::make('is_published')->label('Published'),
            ])
            ->actions([
                // The clients page, where the client is one of the cards.
                Tables\Actions\Action::make('view')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->visible(fn (Client $record) => $record->is_published)
                    ->url(fn (Client $record) => $record->url, shouldOpenInNewTab: true),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
