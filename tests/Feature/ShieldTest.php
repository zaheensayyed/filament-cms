<?php

use BezhanSalleh\FilamentShield\Resources\RoleResource;
use BezhanSalleh\FilamentShield\Resources\RoleResource\Pages\ListRoles;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use zaheensayyed\FilamentCms\Models\ContactFormSubmission;
use zaheensayyed\FilamentCms\Models\Gallery;
use zaheensayyed\FilamentCms\Models\GalleryImage;
use zaheensayyed\FilamentCms\Models\Navigation;
use zaheensayyed\FilamentCms\Models\NavigationItem;
use zaheensayyed\FilamentCms\Models\Page;
use zaheensayyed\FilamentCms\Pages\Settings;
use zaheensayyed\FilamentCms\Resources\ContactSubmissionResource;
use zaheensayyed\FilamentCms\Resources\ContactSubmissionResource\Pages\ListContactSubmissions;
use zaheensayyed\FilamentCms\Resources\GalleryResource;
use zaheensayyed\FilamentCms\Resources\GalleryResource\Pages\CreateGallery;
use zaheensayyed\FilamentCms\Resources\GalleryResource\Pages\EditGallery;
use zaheensayyed\FilamentCms\Resources\GalleryResource\RelationManagers\ImagesRelationManager;
use zaheensayyed\FilamentCms\Resources\NavigationResource;
use zaheensayyed\FilamentCms\Resources\NavigationResource\Pages\CreateNavigation;
use zaheensayyed\FilamentCms\Resources\NavigationResource\Pages\EditNavigation;
use zaheensayyed\FilamentCms\Resources\NavigationResource\RelationManagers\ItemsRelationManager;
use zaheensayyed\FilamentCms\Resources\PageResource;
use zaheensayyed\FilamentCms\Resources\PageResource\Pages\CreatePage;
use zaheensayyed\FilamentCms\Resources\PageResource\Pages\EditPage;
use zaheensayyed\FilamentCms\Resources\PageResource\Pages\ListPages;
use zaheensayyed\FilamentCms\Resources\UserResource;
use zaheensayyed\FilamentCms\Resources\UserResource\Pages\CreateUser;
use zaheensayyed\FilamentCms\Resources\UserResource\Pages\ListUsers;
use zaheensayyed\FilamentCms\Shield\CmsRoles;
use zaheensayyed\FilamentCms\Tests\Fixtures\User;

function permissionSnapshot(): array
{
    return [
        'permissions' => Permission::orderBy('id')->get(['id', 'name', 'guard_name', 'updated_at'])->toArray(),
        'roles' => Role::orderBy('id')->get(['id', 'name', 'guard_name', 'updated_at'])->toArray(),
        'role_has_permissions' => DB::table('role_has_permissions')->orderBy('role_id')->orderBy('permission_id')->get()->toArray(),
    ];
}

function cmsNavigation(): Navigation
{
    return Navigation::create(['key' => 'main-menu', 'name' => 'Main Menu', 'description' => '-', 'created_by' => 1]);
}

describe('default roles', function () {
    it('seeds admin as the Shield super admin with every package permission', function () {
        expect(config('filament-shield.super_admin.name'))->toBe('admin')
            ->and(Role::findByName('admin')->permissions->pluck('name')->sort()->values()->all())->toBe(Permission::pluck('name')->sort()->values()->all())
            ->and(Permission::pluck('name')->all())->toContain(
                'view_any_page',
                'delete_any_navigation',
                'update_gallery',
                'page_Settings',
                'view_any_contact::submission',
                'delete_contact::submission',
                'create_user',
                'view_any_role',
                'update_role',
            );
    });

    it('seeds content_manager with exactly CRUD on pages, menus, galleries and read-only submissions', function () {
        $crud = fn (string $entity) => array_map(fn ($p) => "{$p}_{$entity}", ['view', 'view_any', 'create', 'update', 'delete', 'delete_any']);

        expect(Role::findByName('content_manager')->permissions->pluck('name')->sort()->values()->all())
            ->toBe(collect([
                ...$crud('page'), ...$crud('navigation'), ...$crud('gallery'),
                'view_contact::submission', 'view_any_contact::submission',
            ])->sort()->values()->all());
    });

    it('changes nothing when run again', function () {
        $before = permissionSnapshot();

        $this->travel(1)->minutes();
        $stats = CmsRoles::sync();
        $this->artisan('filament-cms:roles')->assertSuccessful();

        expect($stats)->toBe(['permissions_created' => 0, 'roles_created' => 0, 'permissions_granted' => 0])
            ->and(permissionSnapshot())->toEqual($before);
    });

    it('keeps permissions an operator added and restores missing ones', function () {
        Role::findByName('content_manager')->givePermissionTo('page_Settings');
        Permission::where('name', 'delete_contact::submission')->delete();

        CmsRoles::sync();

        expect(Role::findByName('content_manager')->hasPermissionTo('page_Settings'))->toBeTrue()
            ->and(Permission::where('name', 'delete_contact::submission')->exists())->toBeTrue()
            ->and(Role::findByName('admin')->hasPermissionTo('delete_contact::submission'))->toBeTrue();
    });

    it('makes a user admin from the command line', function () {
        $user = $this->createUser();

        $this->artisan('filament-cms:roles', ['--admin' => $user->email])->assertSuccessful();

        expect($user->fresh()->hasRole('admin'))->toBeTrue();
    });
});

describe('content_manager', function () {
    beforeEach(fn () => $this->actingAs($this->createUser('content_manager')));

    it('can create, edit and delete a page', function () {
        Livewire::test(CreatePage::class)
            ->fillForm(['title' => 'About', 'slug' => 'about'])
            ->call('create')
            ->assertHasNoFormErrors();

        $page = Page::sole();

        Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
            ->fillForm(['title' => 'About us'])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($page->fresh()->title)->toBe('About us');

        Livewire::test(ListPages::class)->callTableAction(EditAction::class, $page)->assertOk();
        Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])->callAction('delete');

        expect(Page::count())->toBe(0);
    });

    it('can manage menus and their items', function () {
        Livewire::test(CreateNavigation::class)
            ->fillForm(['name' => 'Footer', 'key' => 'footer', 'description' => 'Footer links'])
            ->call('create')
            ->assertHasNoFormErrors();

        $navigation = Navigation::sole();
        $page = Page::create(['title' => 'Home', 'slug' => 'home', 'created_by' => 1]);

        $items = Livewire::test(ItemsRelationManager::class, ['ownerRecord' => $navigation, 'pageClass' => EditNavigation::class])
            ->assertTableActionVisible(CreateAction::class)
            ->callTableAction(CreateAction::class, data: ['name' => 'Home', 'slug' => 'home', 'type' => 'page', 'type_id' => $page->id, 'child_menu_items' => []])
            ->assertHasNoTableActionErrors();

        $item = NavigationItem::sole();

        $items->assertTableActionVisible(EditAction::class, $item)
            ->callTableAction(DeleteAction::class, $item);

        expect(NavigationItem::count())->toBe(0);
    });

    it('can manage galleries and their images', function () {
        Livewire::test(CreateGallery::class)
            ->fillForm(['name' => 'Events', 'slug' => 'events', 'images' => []])
            ->call('create')
            ->assertHasNoFormErrors();

        $gallery = Gallery::sole();
        $image = GalleryImage::create(['gallery_id' => $gallery->id, 'image_name' => 'events/a.jpg', 'created_by' => 1]);

        Livewire::test(ImagesRelationManager::class, ['ownerRecord' => $gallery, 'pageClass' => EditGallery::class])
            ->assertTableActionVisible(DeleteAction::class, $image)
            ->callTableAction(DeleteAction::class, $image);

        expect(GalleryImage::count())->toBe(0);
    });

    it('can open contact submissions but not delete them', function () {
        $submission = ContactFormSubmission::create(['name' => 'A', 'email' => 'a@example.com', 'message' => 'Hi']);

        Livewire::test(ListContactSubmissions::class)
            ->assertOk()
            ->assertCanSeeTableRecords([$submission])
            ->assertTableActionVisible(ViewAction::class, $submission)
            ->assertTableActionHidden(DeleteAction::class, $submission)
            ->assertTableBulkActionHidden(DeleteBulkAction::class);

        expect(ContactSubmissionResource::canDelete($submission))->toBeFalse()
            ->and(ContactSubmissionResource::canViewAny())->toBeTrue();
    });

    it('gets 403 and no navigation entry for Settings, Users and Roles', function () {
        Livewire::test(Settings::class)->assertForbidden();
        Livewire::test(ListUsers::class)->assertForbidden();
        Livewire::test(ListRoles::class)->assertForbidden();

        $this->get(Settings::getUrl())->assertForbidden();
        $this->get(UserResource::getUrl())->assertForbidden();
        $this->get(RoleResource::getUrl())->assertForbidden();

        expect(Settings::canAccess())->toBeFalse()
            // Filament only lists a resource in the navigation when canViewAny() passes.
            ->and(UserResource::canViewAny())->toBeFalse()
            ->and(RoleResource::canViewAny())->toBeFalse()
            ->and(PageResource::canViewAny())->toBeTrue();
    });
});

describe('admin', function () {
    it('can do everything, including settings, users, roles and deleting submissions', function () {
        $submission = ContactFormSubmission::create(['name' => 'A', 'email' => 'a@example.com', 'message' => 'Hi']);

        Livewire::test(Settings::class)->assertOk();
        Livewire::test(ListRoles::class)->assertOk();
        Livewire::test(ListContactSubmissions::class)->callTableAction(DeleteAction::class, $submission);

        expect(ContactFormSubmission::count())->toBe(0);

        foreach ([PageResource::class, NavigationResource::class, GalleryResource::class, UserResource::class, RoleResource::class] as $resource) {
            expect($resource::canViewAny())->toBeTrue()
                ->and($resource::canCreate())->toBeTrue();
        }
    });

    it('creates users with roles and a hashed password', function () {
        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Editor',
                'email' => 'editor@example.com',
                'password' => 'secret-password',
                'roles' => [Role::findByName('content_manager')->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::where('email', 'editor@example.com')->sole();

        expect(Hash::check('secret-password', $user->password))->toBeTrue()
            ->and($user->hasRole('content_manager'))->toBeTrue();
    });
});

describe('relation managers respect the parent permissions', function () {
    it('hides item and image actions from a view-only role', function () {
        Role::create(['name' => 'viewer', 'guard_name' => 'web'])
            ->givePermissionTo(['view_any_navigation', 'view_navigation', 'view_any_gallery', 'view_gallery']);

        $this->actingAs($this->createUser('viewer'));

        $navigation = cmsNavigation();
        $item = NavigationItem::create(['navigation_id' => $navigation->id, 'name' => 'Home', 'slug' => 'home', 'level' => 1, 'type' => 'static', 'created_by' => 1]);
        $gallery = Gallery::create(['name' => 'Events', 'slug' => 'events', 'created_by' => 1]);
        $image = GalleryImage::create(['gallery_id' => $gallery->id, 'image_name' => 'events/a.jpg', 'created_by' => 1]);

        Livewire::test(ItemsRelationManager::class, ['ownerRecord' => $navigation, 'pageClass' => EditNavigation::class])
            ->assertCanSeeTableRecords([$item])
            ->assertTableActionHidden(CreateAction::class)
            ->assertTableActionHidden(EditAction::class, $item)
            ->assertTableActionHidden(DeleteAction::class, $item);

        Livewire::test(ImagesRelationManager::class, ['ownerRecord' => $gallery, 'pageClass' => EditGallery::class])
            ->assertCanSeeTableRecords([$image])
            ->assertTableActionHidden(DeleteAction::class, $image);

        expect(NavigationResource::canCreate())->toBeFalse()
            ->and(NavigationResource::canEdit($navigation))->toBeFalse();
    });

    it('denies a user without roles everywhere', function () {
        $this->actingAs($this->createUser());

        foreach ([PageResource::class, NavigationResource::class, GalleryResource::class, ContactSubmissionResource::class, UserResource::class, RoleResource::class] as $resource) {
            expect($resource::canViewAny())->toBeFalse();
        }

        Livewire::test(ListPages::class)->assertForbidden();
    });
});
