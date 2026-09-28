# Users, roles & permissions

Access control is built on [Filament Shield](https://github.com/bezhansalleh/filament-shield)
(and `spatie/laravel-permission` underneath). Both are installed with the package; there is no
separate Shield setup.

## Setup

1. Add the `HasRoles` trait to your user model:

   ```php
   // app/Models/User.php
   use Spatie\Permission\Traits\HasRoles;

   class User extends Authenticatable
   {
       use HasRoles;
       // ...
   }
   ```

2. Run the installer (safe to re-run on upgrades):

   ```bash
   php artisan filament-cms:install
   ```

   It publishes the Shield and permission config and migrations, runs the migrations, and
   creates the permissions and default roles.

3. Give yourself the admin role:

   ```bash
   php artisan filament-cms:roles --admin=you@example.com
   ```

   `php artisan shield:super-admin --user=<id>` does the same.

Existing users get no role automatically, so until you assign one they can't open any CMS
screen.

## Default roles

| Role | Can |
| --- | --- |
| `admin` | Everything: all CMS resources, Settings, Contact Submissions, **Users** and **Roles**. It is Shield's super-admin role, so resources added later are covered automatically. |
| `content_manager` | Create, view, edit and delete Pages, Navigations (and their items) and Galleries (and their images); view Contact Submissions. No Settings, Users or Roles. |

Change or add roles in the panel under **Roles**. Edit users and their roles under **Users**
(create users, reset passwords, assign roles); only admins see these screens.

## Permissions

Permissions follow Shield's naming, `{action}_{resource}`:

| Resource / page | Permissions |
| --- | --- |
| Pages | `view_page`, `view_any_page`, `create_page`, `update_page`, `delete_page`, `delete_any_page` |
| Navigations | same actions with `navigation` |
| Galleries | same actions with `gallery` |
| Contact Submissions | `view_contact::submission`, `view_any_contact::submission`, `delete_contact::submission`, `delete_any_contact::submission` |
| Users | same actions as Pages with `user` |
| Roles | same actions as Pages with `role` |
| Settings page | `page_Settings` |

Menu items and gallery images have no permissions of their own: seeing them needs `view_*` on
the menu/gallery, and adding, editing or deleting them needs `update_*` on it. Every action in
the panel (header create buttons, row and bulk actions, relation manager actions) checks these
policies and is hidden or returns **403** when the permission is missing.

## Re-running and upgrades

```bash
php artisan filament-cms:roles
```

creates any permission or default role that is missing (e.g. after an upgrade adds a
resource) and gives the default roles their missing permissions. It never removes anything,
so permissions you granted on the Roles screen stay, and running it twice changes nothing.

## Configuration

Role names live in `config/filament-cms.php`:

```php
'shield' => [
    'admin_role' => 'admin',                     // Shield's super-admin role
    'content_manager_role' => 'content_manager',
],
```

The package guards its own models with policies from the package. If your app defines its
own `UserPolicy` or `RolePolicy`, those are used instead.

For your **own** resources, generate permissions and policies with Shield as usual:

```bash
php artisan shield:generate --all
```
