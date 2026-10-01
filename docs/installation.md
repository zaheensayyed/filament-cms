# Installation & panel setup

## Requirements

| | Supported |
| --- | --- |
| PHP | 8.1, 8.2, 8.3 |
| Laravel | 10.x |
| Filament | 3.x (tested with 3.3) |
| Database | MySQL / MariaDB, SQLite |

You need a working Filament panel. If the app doesn't have one yet:

```bash
composer require filament/filament:"^3.3" -W
php artisan filament:install --panels
php artisan make:filament-user
```

## 1. Install the package

```bash
composer require zaheensayyed/filament-cms
```

Add the `HasRoles` trait to your user model (the CMS uses roles and permissions, see
[Users, roles & permissions](roles.md)):

```php
// app/Models/User.php
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
    // ...
}
```

Then run the installer:

```bash
php artisan filament-cms:install
```

It publishes `config/filament-cms.php` plus the Shield and permission config and migrations,
runs the migrations (pages, navigations, galleries, settings, contact submissions, roles and
permissions) and creates the `admin` and `content_manager` roles. It is safe to run again on
upgrades.

Give your own account the admin role:

```bash
php artisan filament-cms:roles --admin=you@example.com
```

Galleries and share images are stored on Filament's default disk (`public`), so link it once:

```bash
php artisan storage:link
```

## 2. Register the plugin in your panel

`app/Providers/Filament/AdminPanelProvider.php`:

```php
use zaheensayyed\FilamentCms\FilamentCmsPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...your existing panel setup
        ->plugins([
            FilamentCmsPlugin::make(),
        ]);
}
```

The panel now has **Pages**, **Navigations**, **Galleries**, **Contact Submissions**, a
**Settings** page, and **Users** and **Roles** for admins. The plugin also registers Filament
Shield, so don't add `FilamentShieldPlugin` yourself.

### Optional: CMS theme

`FilamentCmsTheme` gives the panel the DM Sans font and an amber colour palette:

```php
use zaheensayyed\FilamentCms\FilamentCmsPlugin;
use zaheensayyed\FilamentCms\FilamentCmsTheme;

->plugins([
    FilamentCmsPlugin::make(),
    FilamentCmsTheme::make(),
])
```

## 3. Serve CMS pages on the frontend

Turn on the package's catch-all route so pages and galleries are reachable by their slug
(details in [Routing & rendering pages](routing-and-pages.md)):

```dotenv
# .env
FILAMENT_CMS_ROUTES=true
```

## Next steps

- [Concepts](concepts.md): what pages, navigations, galleries and settings are
- [Rendering menus in Blade](menus.md)
- [Routing & rendering pages](routing-and-pages.md)
- [Users, roles & permissions](roles.md)
