<?php

namespace zaheensayyed\FilamentCms\Resources\NavigationResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use zaheensayyed\FilamentCms\Resources\NavigationResource;

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
