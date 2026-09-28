<?php

namespace zaheensayyed\FilamentCms\Policies;

use Filament\Resources\Resource;
use Illuminate\Auth\Access\HandlesAuthorization;
use zaheensayyed\FilamentCms\Shield\CmsPermissions;

/**
 * Maps policy abilities to Shield permissions of one resource, e.g. update → "update_page".
 * Abilities the package doesn't use (restore, replicate, reorder, force delete) are denied;
 * the admin role still passes them through the super-admin gate.
 */
abstract class ResourcePolicy
{
    use HandlesAuthorization;

    /**
     * @return class-string<\Filament\Resources\Resource>
     */
    abstract protected function resource(): string;

    protected function allows($user, string $prefix): bool
    {
        return $user->can(CmsPermissions::name($prefix, $this->resource()));
    }

    public function viewAny($user): bool
    {
        return $this->allows($user, 'view_any');
    }

    public function view($user, $record): bool
    {
        return $this->allows($user, 'view');
    }

    public function create($user): bool
    {
        return $this->allows($user, 'create');
    }

    public function update($user, $record): bool
    {
        return $this->allows($user, 'update');
    }

    public function delete($user, $record): bool
    {
        return $this->allows($user, 'delete');
    }

    public function deleteAny($user): bool
    {
        return $this->allows($user, 'delete_any');
    }

    public function restore($user, $record): bool
    {
        return false;
    }

    public function restoreAny($user): bool
    {
        return false;
    }

    public function forceDelete($user, $record): bool
    {
        return false;
    }

    public function forceDeleteAny($user): bool
    {
        return false;
    }

    public function replicate($user, $record): bool
    {
        return false;
    }

    public function reorder($user): bool
    {
        return false;
    }
}
