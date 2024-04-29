<?php

namespace zaheensayyed\FilamentCms\Resources\PageResource\Pages;

use zaheensayyed\FilamentCms\Resources\PageResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use zaheensayyed\FilamentCms\Traits\CommonResourceTrait;

class CreatePage extends CreateRecord
{
    use CommonResourceTrait;
    
    protected static string $resource = PageResource::class;
}
