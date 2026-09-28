<?php

namespace zaheensayyed\FilamentCms\Pages;

use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use zaheensayyed\FilamentCms\FilamentCms;
use zaheensayyed\FilamentCms\FilamentCmsPlugin;
use zaheensayyed\FilamentCms\Settings\SettingsGroup;
use zaheensayyed\FilamentCms\Shield\CmsPermissions;

class Settings extends Page
{
    use InteractsWithFormActions;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $slug = 'cms-settings';

    protected static string $view = 'filament-cms::pages.settings';

    public ?array $data = [];

    /**
     * Needs the Shield page permission "page_Settings". Filament answers 403 on every request
     * (initial load and Livewire updates) when this is false, and hides the menu entry.
     */
    public static function canAccess(): bool
    {
        return (bool) Filament::auth()->user()?->can(CmsPermissions::pagePermission(static::class));
    }

    public function mount(): void
    {
        $this->form->fill(FilamentCms::settings());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('settings')
                    ->tabs(array_map(
                        fn (string $group) => $this->makeTab($group),
                        FilamentCmsPlugin::get()->getSettingsGroups(),
                    ))
                    ->persistTabInQueryString(),
            ])
            ->statePath('data');
    }

    /**
     * @param  class-string<SettingsGroup>  $group
     */
    protected function makeTab(string $group): Tabs\Tab
    {
        return Tabs\Tab::make($group::label())
            ->icon($group::icon())
            ->statePath($group::key())
            ->schema($group::schema());
    }

    public function save(): void
    {
        FilamentCms::saveSettings($this->form->getState());

        Notification::make()
            ->success()
            ->title('Settings saved')
            ->send();
    }

    public function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save')
                ->submit('save'),
        ];
    }
}
