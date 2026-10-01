<?php

namespace zaheensayyed\FilamentCms\Resources;

use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use zaheensayyed\FilamentCms\Models\Navigation;
use zaheensayyed\FilamentCms\Resources\NavigationResource\Pages;
use zaheensayyed\FilamentCms\Resources\NavigationResource\RelationManagers\ItemsRelationManager;
use zaheensayyed\FilamentCms\Shield\CmsPermissions;

class NavigationResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Navigation::class;

    public static function getPermissionPrefixes(): array
    {
        return CmsPermissions::RESOURCE_PREFIXES;
    }

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                        if (blank($get('key'))) {
                            $set('key', Str::slug($state));
                        }
                    }),
                TextInput::make('key')
                    ->helperText('Stable identifier used in code: FilamentCms::getMenu("main-menu"). Changing the name will not break it.')
                    ->alphaDash()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Textarea::make('description')->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('name'),
                TextColumn::make('key')->badge(),
                TextColumn::make('createdBy.name')->description(fn (Navigation $record) => $record->created_at),
                TextColumn::make('updatedBy.name')->description(fn (Navigation $record) => $record->updated_at),
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
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNavigations::route('/'),
            'create' => Pages\CreateNavigation::route('/create'),
            'edit' => Pages\EditNavigation::route('/{record}/edit'),
        ];
    }
}
