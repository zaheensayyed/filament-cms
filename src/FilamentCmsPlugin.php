<?php

namespace zaheensayyed\FilamentCms;

use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Contracts\Plugin;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Resources\Resource;
use zaheensayyed\FilamentCms\Pages\Settings;
use zaheensayyed\FilamentCms\Resources\ContactSubmissionResource;
use zaheensayyed\FilamentCms\Resources\GalleryResource;
use zaheensayyed\FilamentCms\Resources\NavigationResource;
use zaheensayyed\FilamentCms\Resources\PageResource;
use zaheensayyed\FilamentCms\Resources\UserResource;
use zaheensayyed\FilamentCms\Settings\Groups\CompanyInfoGroup;
use zaheensayyed\FilamentCms\Settings\Groups\ContactFormGroup;
use zaheensayyed\FilamentCms\Settings\Groups\SeoDefaultsGroup;
use zaheensayyed\FilamentCms\Settings\SettingsGroup;

class FilamentCmsPlugin implements Plugin
{
    /**
     * @var array<int, class-string<\Filament\Resources\Resource>>
     */
    public const RESOURCES = [
        NavigationResource::class,
        PageResource::class,
        GalleryResource::class,
        ContactSubmissionResource::class,
    ];

    /**
     * @var array<int, class-string<Page>>
     */
    public const PAGES = [
        Settings::class,
    ];

    /**
     * Each group is rendered as one tab on the Settings page, in this order.
     *
     * @var array<class-string<SettingsGroup>>
     */
    protected array $settingsGroups = [
        CompanyInfoGroup::class,
        SeoDefaultsGroup::class,
        ContactFormGroup::class,
    ];

    public function getId(): string
    {
        return 'filament-cms';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([...static::RESOURCES, UserResource::class])
            ->pages(static::PAGES);

        // Shield's Roles resource comes with the CMS; no separate Shield setup in the app.
        if (! $panel->hasPlugin('filament-shield')) {
            $panel->plugin(FilamentShieldPlugin::make());
        }
    }

    public function boot(Panel $panel): void
    {
        //
    }

    /**
     * Append extra settings tabs, e.g. FilamentCmsPlugin::make()->settingsGroups([SeoGroup::class]).
     *
     * @param  array<class-string<SettingsGroup>>  $groups
     */
    public function settingsGroups(array $groups): static
    {
        $this->settingsGroups = array_values(array_unique([...$this->settingsGroups, ...$groups]));

        return $this;
    }

    /**
     * @return array<class-string<SettingsGroup>>
     */
    public function getSettingsGroups(): array
    {
        return $this->settingsGroups;
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
