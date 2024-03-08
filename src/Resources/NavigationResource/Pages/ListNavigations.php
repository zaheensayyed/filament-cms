<?php

namespace zaheensayyed\FilamentCms\Resources\NavigationResource\Pages;

use zaheensayyed\FilamentCms\Resources\NavigationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNavigations extends ListRecords
{
    protected static string $resource = NavigationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
