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
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use zaheensayyed\FilamentCms\Commands\FilamentCmsCommand;
use zaheensayyed\FilamentCms\Models\ContactFormSubmission;
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
                // (see packageBooted), so install only needs to run them.
                $command
                    ->publishConfigFile()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('zaheensayyed/filament-cms');
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

    public function packageRegistered(): void {}

    public function packageBooted(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

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
