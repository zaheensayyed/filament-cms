<?php

namespace zaheensayyed\FilamentCms\Policies;

use zaheensayyed\FilamentCms\Resources\GalleryResource;

class GalleryPolicy extends ResourcePolicy
{
    protected function resource(): string
    {
        return GalleryResource::class;
    }
}
