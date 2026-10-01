<?php

namespace zaheensayyed\FilamentCms\Settings\Groups;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use zaheensayyed\FilamentCms\Settings\SettingsGroup;

class CompanyInfoGroup extends SettingsGroup
{
    public static function key(): string
    {
        return 'company';
    }

    public static function label(): string
    {
        return 'Company Info';
    }

    public static function icon(): ?string
    {
        return 'heroicon-o-building-office';
    }

    public static function schema(): array
    {
        return [
            TextInput::make('contact_no')
                ->label('Contact number')
                ->required()
                ->maxLength(30),
            TextInput::make('email')
                ->label('Email address')
                ->email()
                ->required(),
            Textarea::make('address')
                ->label('Address')
                ->rows(3)
                ->maxLength(500),
            Textarea::make('description')
                ->label('Company description')
                ->rows(5)
                ->maxLength(1000),
        ];
    }
}
