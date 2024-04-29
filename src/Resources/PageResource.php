<?php

namespace zaheensayyed\FilamentCms\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Illuminate\Database\Eloquent\Builder;
use zaheensayyed\FilamentCms\Models\Page;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use zaheensayyed\FilamentCms\ResourcesPageResource\RelationManagers;
use Filament\Forms\Components\FileUpload;
use zaheensayyed\FilamentCms\Resources\PageResource\Pages;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make('title')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->live(debounce:1000)
                            ->afterStateUpdated(function(Set $set, $state){
                                $set('slug', Str::slug($state));
                            }),
                        TextInput::make('slug')->required(),
                    ])
                    ->columns(2),
                RichEditor::make('body')
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('title'),
                TextColumn::make('slug'),
                TextColumn::make('createdBy.name')->description(fn(Page $record) => $record->created_at),
                TextColumn::make('updatedBy.name')->description(fn(Page $record) => $record->updated_at),
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
