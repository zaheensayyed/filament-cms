<?php

namespace zaheensayyed\FilamentCms\Resources;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use zaheensayyed\FilamentCms\Models\Gallery;
use zaheensayyed\FilamentCms\Resources\GalleryResource\Pages;
use zaheensayyed\FilamentCms\Resources\GalleryResource\RelationManagers\ImagesRelationManager;

// use zaheensayyed\FilamentCms\Resources\GalleryResource\RelationManagers;

class GalleryResource extends Resource
{
    protected static ?string $model = Gallery::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->live(debounce: 1000)
                    ->afterStateUpdated(function (Set $set, $state) {
                        $set('slug', Str::slug($state));
                    }),
                TextInput::make('slug')->required(),
                Textarea::make('description'),
                Grid::make('images')
                    ->schema([
                        FileUpload::make('images')
                            ->multiple()
                            ->directory(function (callable $get) {
                                return $get('slug');
                            })
                            ->image()
                            ->imageResizeTargetWidth('1920')
                            ->imageResizeTargetHeight('1080')
                            // ->panelAspectRatio('1:1')
                            // ->panelLayout('compact')
                            ->minSize(1)
                            ->maxSize(2048),
                    ])
                    ->columns(1),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('name')->searchable(),
                TextColumn::make('createdBy.name')->description(fn (Gallery $record) => $record->created_at),
                TextColumn::make('updatedBy.name')->description(fn (Gallery $record) => $record->updated_at),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            ImagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGalleries::route('/'),
            'create' => Pages\CreateGallery::route('/create'),
            'edit' => Pages\EditGallery::route('/{record}/edit'),
        ];
    }
}
