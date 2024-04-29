<?php

namespace zaheensayyed\FilamentCms\Resources\GalleryResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use zaheensayyed\FilamentCms\Models\GalleryImage;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Placeholder::make('image_name')
                    ->label('Preview')
                    ->content(function(GalleryImage $record){
                        $imagePath = "/storage/{$record->image_name}";
                        return new HtmlString("<img src='{$imagePath}'>");
                    }),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('image')
            ->columns([
                Tables\Columns\ImageColumn::make('image_name')->label('Image'),
                TextColumn::make('createdBy.name')->description(fn(GalleryImage $record) => $record->created_at),
                TextColumn::make('updatedBy.name')->description(fn(GalleryImage $record) => $record->updated_at),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected function canCreate(): bool{
        return false;
    }
}
