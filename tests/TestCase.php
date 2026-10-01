<?php

namespace zaheensayyed\FilamentCms\Tests;

use BezhanSalleh\FilamentShield\FilamentShieldServiceProvider;
use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\Facades\Filament;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;
use Spatie\Permission\PermissionServiceProvider;
use zaheensayyed\FilamentCms\FilamentCmsServiceProvider;
use zaheensayyed\FilamentCms\Shield\CmsPermissions;
use zaheensayyed\FilamentCms\Shield\CmsRoles;
use zaheensayyed\FilamentCms\Tests\Fixtures\AdminPanelProvider;
use zaheensayyed\FilamentCms\Tests\Fixtures\User;

class TestCase extends Orchestra
{
    use InteractsWithViews;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'zaheensayyed\\FilamentCms\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        CmsRoles::sync();

        // Panel tests run as the admin role unless a test logs in as someone else.
        $this->actingAs($this->createUser(CmsPermissions::adminRole()));
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    public function createUser(?string $role = null): User
    {
        $user = User::create([
            'name' => $role ?? 'No Role',
            'email' => ($role ?? 'nobody') . '-' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
        ]);

        if ($role) {
            $user->assignRole($role);
        }

        return $user;
    }

    protected function getPackageProviders($app)
    {
        return [
            ActionsServiceProvider::class,
            BladeCaptureDirectiveServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            FilamentServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            LivewireServiceProvider::class,
            NotificationsServiceProvider::class,
            SupportServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,
            PermissionServiceProvider::class,
            FilamentShieldServiceProvider::class,
            FilamentCmsServiceProvider::class,
            AdminPanelProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('cache.default', 'array');
        // Serialize like the file/redis stores do, so cached models are tested for real.
        config()->set('cache.stores.array.serialize', true);
        config()->set('app.key', 'base64:' . base64_encode(str_repeat('a', 32)));
        config()->set('filament-cms.routes.enabled', true);
        config()->set('auth.providers.users.model', User::class);

        /*
        $migration = include __DIR__.'/../database/migrations/create_filament-cms_table.php.stub';
        $migration->up();
        */
    }
}
