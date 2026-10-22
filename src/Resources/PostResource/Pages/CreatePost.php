<?php

namespace zaheensayyed\FilamentCms\Resources\PostResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use zaheensayyed\FilamentCms\Resources\PostResource;
use zaheensayyed\FilamentCms\Traits\CommonResourceTrait;

class CreatePost extends CreateRecord
{
    use CommonResourceTrait;

    protected static string $resource = PostResource::class;
}
