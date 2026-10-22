<?php

namespace zaheensayyed\FilamentCms\Resources\NavigationResource\Pages;

use Filament\Resources\Pages\ListRecords;
use zaheensayyed\FilamentCms\Models\Navigation;
use zaheensayyed\FilamentCms\Resources\NavigationResource;

class ListNavigations extends ListRecords
{
    protected static string $resource = NavigationResource::class;

    public function mount(): void
    {
        Navigation::ensureFixed();

        parent::mount();
    }
}
