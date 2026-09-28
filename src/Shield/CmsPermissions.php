<?php

namespace zaheensayyed\FilamentCms\Shield;

use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use BezhanSalleh\FilamentShield\Resources\RoleResource;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Facades\Filament;
use zaheensayyed\FilamentCms\FilamentCmsPlugin;
use zaheensayyed\FilamentCms\Resources\ContactSubmissionResource;
use zaheensayyed\FilamentCms\Resources\GalleryResource;
use zaheensayyed\FilamentCms\Resources\NavigationResource;
use zaheensayyed\FilamentCms\Resources\PageResource;
use zaheensayyed\FilamentCms\Resources\UserResource;

/**
 * Permission names (Shield's `{prefix}_{identifier}` format) for every package resource
 * and page, and the default roles built from them.
 */
class CmsPermissions
{
    public const RESOURCE_PREFIXES = ['view', 'view_any', 'create', 'update', 'delete', 'delete_any'];

    public static function adminRole(): string
    {
        return config('filament-cms.shield.admin_role', 'admin');
    }

    public static function contentManagerRole(): string
    {
        return config('filament-cms.shield.content_manager_role', 'content_manager');
    }

    public static function guard(): string
    {
        return Filament::getDefaultPanel()?->getAuthGuard() ?? config('auth.defaults.guard', 'web');
    }

    /**
     * @param  class-string  $resource
     */
    public static function identifier(string $resource): string
    {
        return FilamentShield::getPermissionIdentifier($resource);
    }

    /**
     * @param  class-string  $resource
     */
    public static function name(string $prefix, string $resource): string
    {
        return $prefix . '_' . static::identifier($resource);
    }

    /**
     * Resources guarded by the package. Only classes that exist are listed, so the permissions
     * of resources added by later releases are created when the sync runs again.
     *
     * @return array<int, class-string>
     */
    public static function resources(): array
    {
        return array_values(array_filter(
            [...FilamentCmsPlugin::RESOURCES, UserResource::class, RoleResource::class],
            fn (string $resource) => class_exists($resource),
        ));
    }

    /**
     * @param  class-string  $resource
     * @return array<int, string>
     */
    public static function forResource(string $resource, ?array $prefixes = null): array
    {
        $prefixes ??= is_subclass_of($resource, HasShieldPermissions::class)
            ? $resource::getPermissionPrefixes()
            : static::RESOURCE_PREFIXES;

        return array_map(fn (string $prefix) => static::name($prefix, $resource), $prefixes);
    }

    /**
     * Shield page permissions, e.g. "page_Settings".
     *
     * @return array<int, string>
     */
    public static function pages(): array
    {
        return array_values(array_map(
            fn (string $page) => static::pagePermission($page),
            array_filter(FilamentCmsPlugin::PAGES, fn (string $page) => class_exists($page)),
        ));
    }

    /**
     * @param  class-string  $page
     */
    public static function pagePermission(string $page): string
    {
        return Utils::getPagePermissionPrefix() . '_' . class_basename($page);
    }

    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        return array_values(array_unique([
            ...collect(static::resources())->flatMap(fn (string $resource) => static::forResource($resource)),
            ...static::pages(),
        ]));
    }

    /**
     * Full CRUD on pages, menus and galleries; read-only contact submissions.
     *
     * @return array<int, string>
     */
    public static function contentManager(): array
    {
        return [
            ...static::forResource(PageResource::class),
            ...static::forResource(NavigationResource::class),
            ...static::forResource(GalleryResource::class),
            ...static::forResource(ContactSubmissionResource::class, ['view', 'view_any']),
        ];
    }
}
