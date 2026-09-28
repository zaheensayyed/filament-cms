<?php

namespace zaheensayyed\FilamentCms\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Traits\HasRoles;
use zaheensayyed\FilamentCms\Shield\CmsPermissions;
use zaheensayyed\FilamentCms\Shield\CmsRoles;

class SyncRolesCommand extends Command
{
    public $signature = 'filament-cms:roles
        {--admin= : ID or email of a user to give the admin role}';

    public $description = 'Create the Filament CMS permissions and default roles (safe to re-run)';

    public function handle(): int
    {
        $stats = CmsRoles::sync();

        $this->components->info(sprintf(
            'Roles ready: %d permission(s) created, %d role(s) created, %d permission(s) granted.',
            $stats['permissions_created'],
            $stats['roles_created'],
            $stats['permissions_granted'],
        ));

        $this->components->twoColumnDetail(CmsPermissions::adminRole(), 'everything (super admin)');
        $this->components->twoColumnDetail(CmsPermissions::contentManagerRole(), 'pages, menus, galleries; view contact submissions');

        if ($admin = $this->option('admin')) {
            return $this->assignAdmin($admin);
        }

        return self::SUCCESS;
    }

    protected function assignAdmin(string $idOrEmail): int
    {
        $model = config('auth.providers.users.model');

        if (! in_array(HasRoles::class, class_uses_recursive($model), true)) {
            $this->components->error("{$model} must use the Spatie\\Permission\\Traits\\HasRoles trait.");

            return self::FAILURE;
        }

        $user = $model::query()
            ->where(is_numeric($idOrEmail) ? 'id' : 'email', $idOrEmail)
            ->first();

        if (! $user) {
            $this->components->error("No user found for \"{$idOrEmail}\".");

            return self::FAILURE;
        }

        $user->assignRole(CmsPermissions::adminRole());

        $this->components->info("{$user->email} is now an admin.");

        return self::SUCCESS;
    }
}
