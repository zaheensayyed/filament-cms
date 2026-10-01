<?php

namespace zaheensayyed\FilamentCms\Policies;

/**
 * For records managed inside a parent's relation manager (menu items, gallery images):
 * seeing them needs "view" on the parent resource, changing them needs "update" on it.
 */
abstract class ChildResourcePolicy extends ResourcePolicy
{
    public function viewAny($user): bool
    {
        return $this->allows($user, 'view');
    }

    public function create($user): bool
    {
        return $this->allows($user, 'update');
    }

    public function update($user, $record): bool
    {
        return $this->allows($user, 'update');
    }

    public function delete($user, $record): bool
    {
        return $this->allows($user, 'update');
    }

    public function deleteAny($user): bool
    {
        return $this->allows($user, 'update');
    }
}
