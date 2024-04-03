<?php

namespace zaheensayyed\FilamentCms\Resources\NavigationResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use zaheensayyed\FilamentCms\Resources\NavigationResource;
use zaheensayyed\FilamentCms\Traits\CommonResourceTrait;

class CreateNavigation extends CreateRecord
{
    use CommonResourceTrait;

    protected static string $resource = NavigationResource::class;
}
