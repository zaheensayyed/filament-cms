<?php

namespace zaheensayyed\FilamentCms\Settings;

use Filament\Forms\Components\Component;

/**
 * One settings group = one tab on the Settings page.
 *
 * Field names in schema() become the setting keys, so a field named
 * "email" in the "company" group is read with FilamentCms::setting('company.email').
 */
abstract class SettingsGroup
{
    /**
     * Unique group name, stored in the `group` column (e.g. "company").
     */
    abstract public static function key(): string;

    abstract public static function label(): string;

    public static function icon(): ?string
    {
        return null;
    }

    /**
     * @return array<Component>
     */
    abstract public static function schema(): array;
}
