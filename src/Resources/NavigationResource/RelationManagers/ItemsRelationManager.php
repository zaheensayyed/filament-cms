<?php

namespace zaheensayyed\FilamentCms\Resources\NavigationResource\RelationManagers;

use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use PhpParser\Node\Expr\Cast\Array_;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use zaheensayyed\FilamentCms\Models\NavigationItem;
use Filament\Resources\RelationManagers\RelationManager;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(debounce: 1000),
                TextInput::make('slug')
                    ->required(),
                Select::make('type')
                    ->options([
                        NavigationItem::TYPE_PAGE => 'Page'
                    ])
                    ->default(NavigationItem::TYPE_PAGE)
                    ->required()

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('createdBy.name')->description(fn(NavigationItem $record) => $record->created_at),
                TextColumn::make('updatedBy.name')->description(fn(NavigationItem $record) => $record->updated_at),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data, RelationManager $livewire): array {
                        return static::createdBy($data);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data, RelationManager $livewire): array {
                        return static::updatedBy($data);
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function createdBy(array $data): array
    {
        $data['created_by'] = auth()->id();
        return $data;
    }

    private static function updatedBy(array $data): array
    {
        $data['updated_by'] = auth()->id();
        return $data;
    }
}
