<?php

namespace zaheensayyed\FilamentCms\Policies;

use BezhanSalleh\FilamentShield\Resources\RoleResource;

/**
 * Guards Shield's Roles resource when the app hasn't generated its own RolePolicy.
 */
class RolePolicy extends ResourcePolicy
{
    protected function resource(): string
    {
        return RoleResource::class;
    }
}
