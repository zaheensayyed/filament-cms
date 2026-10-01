<?php

namespace zaheensayyed\FilamentCms\Policies;

use zaheensayyed\FilamentCms\Resources\PageResource;

class PagePolicy extends ResourcePolicy
{
    protected function resource(): string
    {
        return PageResource::class;
    }
}
