<?php

namespace zaheensayyed\FilamentCms\Resources\NavigationResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use zaheensayyed\FilamentCms\Traits\CommonResourceTrait;
use zaheensayyed\FilamentCms\Resources\NavigationResource;

class CreateNavigation extends CreateRecord
{
    use CommonResourceTrait;

    protected static string $resource = NavigationResource::class;
}
