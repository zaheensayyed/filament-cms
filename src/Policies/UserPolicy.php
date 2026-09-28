<?php

namespace zaheensayyed\FilamentCms\Policies;

use zaheensayyed\FilamentCms\Resources\UserResource;

class UserPolicy extends ResourcePolicy
{
    protected function resource(): string
    {
        return UserResource::class;
    }
}
