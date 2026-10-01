<?php

namespace zaheensayyed\FilamentCms\Settings\Groups;

use Closure;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use zaheensayyed\FilamentCms\Contact\ContactForm;
use zaheensayyed\FilamentCms\Settings\SettingsGroup;

class ContactFormGroup extends SettingsGroup
{
    public static function key(): string
    {
        return 'contact_form';
    }

    public static function label(): string
    {
        return 'Contact Form';
    }

    public static function icon(): ?string
    {
        return 'heroicon-o-envelope';
    }

    public static function schema(): array
    {
        return [
            Toggle::make('enabled')
                ->label('Accept contact form submissions')
                // Enabled until someone switches it off, even before the tab is first saved.
                ->afterStateHydrated(fn (Toggle $component, $state) => $component->state($state ?? true)),
            TextInput::make('recipients')
                ->label('Recipients')
                ->helperText('Comma-separated email addresses. Leave empty to use Company Info → Email address.')
                ->maxLength(1000)
                ->rule(fn () => function (string $attribute, $value, Closure $fail) {
                    foreach (ContactForm::parseRecipients($value) as $email) {
                        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                            $fail("\"{$email}\" is not a valid email address.");
                        }
                    }
                }),
            TextInput::make('subject_prefix')
                ->label('Email subject prefix')
                ->placeholder('[Website]')
                ->maxLength(100),
            Textarea::make('success_message')
                ->label('Success message')
                ->placeholder(ContactForm::DEFAULT_SUCCESS_MESSAGE)
                ->helperText('Shown to the visitor after sending the form.')
                ->rows(2)
                ->maxLength(500),
        ];
    }
}
