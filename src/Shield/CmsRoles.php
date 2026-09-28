<?php

namespace zaheensayyed\FilamentCms\Shield;

use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

/**
 * Creates the package permissions and the default roles. Idempotent and additive: it only adds
 * what is missing, so re-running it changes nothing on a configured app and never removes
 * permissions an operator granted through the Roles screen.
 */
class CmsRoles
{
    /**
     * @return array{permissions_created: int, roles_created: int, permissions_granted: int}
     */
    public static function sync(): array
    {
        $registrar = app(PermissionRegistrar::class);
        $registrar->forgetCachedPermissions();

        $guard = CmsPermissions::guard();
        $permissionModel = Utils::getPermissionModel();
        $roleModel = Utils::getRoleModel();

        $stats = ['permissions_created' => 0, 'roles_created' => 0, 'permissions_granted' => 0];

        foreach (CmsPermissions::all() as $name) {
            $permission = $permissionModel::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
            $stats['permissions_created'] += (int) $permission->wasRecentlyCreated;
        }

        $roles = [
            CmsPermissions::adminRole() => CmsPermissions::all(),
            CmsPermissions::contentManagerRole() => CmsPermissions::contentManager(),
        ];

        foreach ($roles as $roleName => $permissions) {
            $role = $roleModel::firstOrCreate(['name' => $roleName, 'guard_name' => $guard]);
            $stats['roles_created'] += (int) $role->wasRecentlyCreated;

            $missing = array_values(array_diff($permissions, $role->permissions()->pluck('name')->all()));

            if ($missing !== []) {
                $role->givePermissionTo($missing);
                $stats['permissions_granted'] += count($missing);
            }
        }

        $registrar->forgetCachedPermissions();

        return $stats;
    }
}
