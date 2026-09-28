<?php

namespace zaheensayyed\FilamentCms\Policies;

use zaheensayyed\FilamentCms\Resources\GalleryResource;

/**
 * Gallery images follow the permissions of their parent resource.
 */
class GalleryImagePolicy extends ChildResourcePolicy
{
    protected function resource(): string
    {
        return GalleryResource::class;
    }
}
