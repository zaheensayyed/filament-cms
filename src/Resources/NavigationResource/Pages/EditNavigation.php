<?php

namespace zaheensayyed\FilamentCms\Resources\NavigationResource\Pages;

use Filament\Resources\Pages\EditRecord;
use zaheensayyed\FilamentCms\Resources\NavigationResource;
use zaheensayyed\FilamentCms\Traits\CommonResourceTrait;

class EditNavigation extends EditRecord
{
    use CommonResourceTrait;

    protected static string $resource = NavigationResource::class;
}
