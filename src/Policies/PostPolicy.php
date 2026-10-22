<?php

namespace zaheensayyed\FilamentCms\Policies;

use zaheensayyed\FilamentCms\Resources\PostResource;

class PostPolicy extends ResourcePolicy
{
    protected function resource(): string
    {
        return PostResource::class;
    }
}
