<?php

namespace zaheensayyed\FilamentCms\Resources\NavigationResource\RelationManagers;

use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Forms\Components\Grid;
use PhpParser\Node\Expr\Cast\Array_;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use zaheensayyed\FilamentCms\Models\NavigationItem;
use Filament\Resources\RelationManagers\RelationManager;
use zaheensayyed\FilamentCms\Models\Navigation;
use zaheensayyed\FilamentCms\Repositories\CommonRepository;
use zaheensayyed\FilamentCms\Repositories\NavigationItemRepository;

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
                    ->live(debounce: 1000)
                    ->afterStateUpdated(function(Set $set, $state){
                        $set('slug', Str::slug($state));
                    }),
                TextInput::make('slug')
                    ->required(),
                Select::make('type')
                    ->options([
                        NavigationItem::TYPE_PAGE => 'Page',
                    ])
                    ->default(NavigationItem::TYPE_PAGE)
                    ->required(),
                Select::make('type_id')
                    ->required()
                    ->options(function(Get $get){
                        $type = $get('type');
                        if($type){
                             return CommonRepository::navigationTypeSelectOptions($type);
                        }
                        return [];
                    }),
                Grid::make('child_items_grid')
                    ->schema([
                        Repeater::make('child_menu_items')
                            ->schema([
                                TextInput::make('child_name')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(debounce: 1000)
                                    ->afterStateUpdated(function(Set $set, Callable $get, $state){
                                        $set('child_slug', Str::slug($state));
                                    }),
                                TextInput::make('child_slug')
                                    ->required(),
                                Select::make('child_type')
                                    ->options([
                                        NavigationItem::TYPE_PAGE => 'Page'
                                    ])
                                    ->default(NavigationItem::TYPE_PAGE)
                                    ->required(),
                                Select::make('child_type_id')
                                    ->required()
                                    ->options(function(Get $get){
                                        $type = $get('child_type');
                                        if($type){
                                            return CommonRepository::navigationTypeSelectOptions($type);
                                        }
                                        return [];
                                    }),
                            ])
                            ->columns(2)
                    ])
                    ->columns(1),
                
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('level', 1))
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('createdBy.name')->description(fn (NavigationItem $record) => $record->created_at),
                TextColumn::make('updatedBy.name')->description(fn (NavigationItem $record) => $record->updated_at),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->using(function (array $data): array {
                        return CommonRepository::mutateDataForCreatedBy($data);
                    })
                    ->after(function ($data, NavigationItem $record) {
                        NavigationItemRepository::afterSave($data, $record);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateRecordDataUsing(function (array $data, NavigationItem $record): array {
                        return NavigationItemRepository::mutateBeforeFill($data, $record); 
                    })
                    ->using(function ($data, NavigationItem $record) {
                        CommonRepository::mutateDataForUpdatedBy($data);
                    })
                    ->after(function ($data, NavigationItem $record) {
                        NavigationItemRepository::afterSave($data, $record);
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
