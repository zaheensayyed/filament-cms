<?php

namespace zaheensayyed\FilamentCms\Policies;

use zaheensayyed\FilamentCms\Resources\ClientResource;

class ClientPolicy extends ResourcePolicy
{
    protected function resource(): string
    {
        return ClientResource::class;
    }

    /**
     * Drag and drop ordering on the Clients list is part of editing clients.
     */
    public function reorder($user): bool
    {
        return $this->allows($user, 'update');
    }
}
