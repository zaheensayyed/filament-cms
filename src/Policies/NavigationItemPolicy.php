<?php

namespace zaheensayyed\FilamentCms\Policies;

use zaheensayyed\FilamentCms\Resources\NavigationResource;

/**
 * Navigation items follow the permissions of their parent resource.
 */
class NavigationItemPolicy extends ChildResourcePolicy
{
    protected function resource(): string
    {
        return NavigationResource::class;
    }
}
