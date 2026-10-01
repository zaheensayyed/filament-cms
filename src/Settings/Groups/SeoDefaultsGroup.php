<?php

namespace zaheensayyed\FilamentCms\Settings\Groups;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use zaheensayyed\FilamentCms\Seo\SeoFields;
use zaheensayyed\FilamentCms\Seo\SeoMeta;
use zaheensayyed\FilamentCms\Settings\SettingsGroup;

class SeoDefaultsGroup extends SettingsGroup
{
    public static function key(): string
    {
        return 'seo';
    }

    public static function label(): string
    {
        return 'SEO Defaults';
    }

    public static function icon(): ?string
    {
        return 'heroicon-o-magnifying-glass';
    }

    public static function schema(): array
    {
        return [
            TextInput::make('site_name')
                ->label('Site name')
                ->helperText('Used for og:site_name and the {site_name} placeholder. Defaults to the app name.')
                ->maxLength(255),
            TextInput::make('title_pattern')
                ->label('Title pattern')
                ->placeholder(SeoMeta::DEFAULT_TITLE_PATTERN)
                ->helperText('Applied when a page has no meta title. Placeholders: {title}, {site_name}.')
                ->maxLength(255),
            SeoFields::withCharacterCounter(
                Textarea::make('default_description')
                    ->label('Default meta description')
                    ->helperText('Recommended 150–160 characters.')
                    ->rows(3)
                    ->maxLength(500),
                160,
            ),
            FileUpload::make('default_og_image')
                ->label('Default social share image')
                ->helperText('Recommended at least 1200×630 px.')
                ->image()
                ->directory('seo')
                ->maxSize(2048),
            Select::make('default_robots')
                ->label('Default robots')
                ->options(SeoMeta::ROBOTS_OPTIONS)
                ->placeholder('index, follow'),
        ];
    }
}
