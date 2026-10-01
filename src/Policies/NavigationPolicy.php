<?php

namespace zaheensayyed\FilamentCms\Policies;

use zaheensayyed\FilamentCms\Resources\NavigationResource;

class NavigationPolicy extends ResourcePolicy
{
    protected function resource(): string
    {
        return NavigationResource::class;
    }
}
