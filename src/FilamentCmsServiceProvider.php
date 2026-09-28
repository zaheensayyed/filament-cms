<?php

namespace zaheensayyed\FilamentCms;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Traits\HasRoles;
use zaheensayyed\FilamentCms\Commands\FilamentCmsCommand;
use zaheensayyed\FilamentCms\Commands\SyncRolesCommand;
use zaheensayyed\FilamentCms\Models\ContactFormSubmission;
use zaheensayyed\FilamentCms\Models\Gallery;
use zaheensayyed\FilamentCms\Models\GalleryImage;
use zaheensayyed\FilamentCms\Models\Navigation;
use zaheensayyed\FilamentCms\Models\NavigationItem;
use zaheensayyed\FilamentCms\Models\Page;
use zaheensayyed\FilamentCms\Policies\ContactFormSubmissionPolicy;
use zaheensayyed\FilamentCms\Policies\GalleryImagePolicy;
use zaheensayyed\FilamentCms\Policies\GalleryPolicy;
use zaheensayyed\FilamentCms\Policies\NavigationItemPolicy;
use zaheensayyed\FilamentCms\Policies\NavigationPolicy;
use zaheensayyed\FilamentCms\Policies\PagePolicy;
use zaheensayyed\FilamentCms\Policies\RolePolicy;
use zaheensayyed\FilamentCms\Policies\UserPolicy;
use zaheensayyed\FilamentCms\Shield\CmsPermissions;
use zaheensayyed\FilamentCms\Testing\TestsFilamentCms;

class FilamentCmsServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-cms';

    public static string $viewNamespace = 'filament-cms';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command) {
                // Package migrations are loaded straight from the vendor folder
                // (see packageBooted); Shield and spatie/laravel-permission publish theirs.
                // Every step is safe to re-run: nothing is published twice or overwritten.
                $command
                    ->startWith(fn (InstallCommand $command) => $this->publishShieldFiles($command))
                    ->publishConfigFile()
                    ->askToStarRepoOnGitHub('zaheensayyed/filament-cms')
                    ->endWith(fn (InstallCommand $command) => $this->finishShieldInstall($command));
            });

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void
    {
        // The CMS admin role is Shield's super-admin role. The gate itself is registered in
        // bootShield() so users without the HasRoles trait don't break every check.
        config()->set('filament-shield.super_admin.name', CmsPermissions::adminRole());
        config()->set('filament-shield.super_admin.define_via_gate', false);
    }

    public function packageBooted(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->bootShield();

        if (config('filament-cms.routes.enabled')) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        }

        $this->bootContactForm();

        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        FilamentAsset::registerScriptData(
            $this->getScriptData(),
            $this->getAssetPackageName()
        );

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        // Handle Stubs
        if (app()->runningInConsole()) {
            foreach (app(Filesystem::class)->files(__DIR__ . '/../stubs/') as $file) {
                $this->publishes([
                    $file->getRealPath() => base_path("stubs/filament-cms/{$file->getFilename()}"),
                ], 'filament-cms-stubs');
            }
        }

        // Testing
        Testable::mixin(new TestsFilamentCms);
    }

    protected function bootShield(): void
    {
        // Admin passes every check, including resources added later.
        Gate::before(function ($user) {
            return method_exists($user, 'hasRole') && $user->hasRole(CmsPermissions::adminRole()) ? true : null;
        });

        Gate::policy(Page::class, PagePolicy::class);
        Gate::policy(Navigation::class, NavigationPolicy::class);
        Gate::policy(NavigationItem::class, NavigationItemPolicy::class);
        Gate::policy(Gallery::class, GalleryPolicy::class);
        Gate::policy(GalleryImage::class, GalleryImagePolicy::class);
        Gate::policy(ContactFormSubmission::class, ContactFormSubmissionPolicy::class);

        // The app's own User / Role policies win; ours only fill the gap.
        $this->app->booted(function () {
            $defaults = [
                config('auth.providers.users.model') => UserPolicy::class,
                app(PermissionRegistrar::class)->getRoleClass() => RolePolicy::class,
            ];

            foreach ($defaults as $model => $policy) {
                if ($model && class_exists($model) && Gate::getPolicyFor($model) === null) {
                    Gate::policy($model, $policy);
                }
            }
        });
    }

    protected function publishShieldFiles(InstallCommand $command): void
    {
        $command->comment('Publishing Shield and permission config and migrations...');

        foreach (['permission-config', 'permission-migrations', 'filament-shield-config'] as $tag) {
            $command->callSilently('vendor:publish', ['--tag' => $tag]);
        }
    }

    protected function finishShieldInstall(InstallCommand $command): void
    {
        if ($command->confirm('Run the migrations and create the default roles now?', true)) {
            $command->call('migrate');
            $command->call('filament-cms:roles');
        } else {
            $command->warn('Later, run: php artisan migrate && php artisan filament-cms:roles');
        }

        $userModel = config('auth.providers.users.model');

        if (! in_array(HasRoles::class, class_uses_recursive($userModel), true)) {
            $command->warn("Add the Spatie\\Permission\\Traits\\HasRoles trait to {$userModel}; without it nobody can open the CMS resources.");
        }

        $command->info('Give yourself the admin role:  php artisan filament-cms:roles --admin=you@example.com');
        $command->line('(or: php artisan shield:super-admin --user=<id>)');
    }

    protected function bootContactForm(): void
    {
        RateLimiter::for('filament-cms-contact', function (Request $request) {
            return Limit::perMinute((int) config('filament-cms.contact_form.rate_limit', 5))->by($request->ip());
        });

        // The mailer fires MessageSent once the transport accepted the message
        // (inline on the sync queue, or later in a queue worker).
        Event::listen(MessageSent::class, function (MessageSent $event) {
            $submission = $event->data['submission'] ?? null;

            if ($submission instanceof ContactFormSubmission) {
                $submission->markAsSent();
            }
        });

        if (config('filament-cms.contact_form.enabled', true)) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/contact.php');
        }
    }

    protected function getAssetPackageName(): ?string
    {
        return 'zaheensayyed/filament-cms';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            // AlpineComponent::make('filament-cms', __DIR__ . '/../resources/dist/components/filament-cms.js'),
            Css::make('filament-cms-styles', __DIR__ . '/../resources/dist/filament-cms.css'),
            Js::make('filament-cms-scripts', __DIR__ . '/../resources/dist/filament-cms.js'),
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            FilamentCmsCommand::class,
            SyncRolesCommand::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getScriptData(): array
    {
        return [];
    }
}
