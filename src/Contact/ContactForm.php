<?php

namespace zaheensayyed\FilamentCms\Contact;

use zaheensayyed\FilamentCms\FilamentCms;

/**
 * Runtime contact form options, read from the "Contact Form" settings tab.
 */
class ContactForm
{
    public const DEFAULT_SUCCESS_MESSAGE = 'Thank you for your message. We will get back to you soon.';

    public const SESSION_KEY = 'filament-cms.contact.success';

    public static function enabled(): bool
    {
        return (bool) FilamentCms::setting('contact_form.enabled', true);
    }

    /**
     * contact_form.recipients (comma-separated), falling back to company.email.
     *
     * @return array<int, string>
     */
    public static function recipients(): array
    {
        $recipients = static::parseRecipients(FilamentCms::setting('contact_form.recipients'));

        if ($recipients === []) {
            $recipients = static::parseRecipients(FilamentCms::setting('company.email'));
        }

        return $recipients;
    }

    /**
     * @return array<int, string>
     */
    public static function parseRecipients(?string $value): array
    {
        return collect(explode(',', (string) $value))
            ->map(fn (string $email) => trim($email))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public static function subjectPrefix(): ?string
    {
        return FilamentCms::setting('contact_form.subject_prefix') ?: null;
    }

    public static function successMessage(): string
    {
        return FilamentCms::setting('contact_form.success_message') ?: static::DEFAULT_SUCCESS_MESSAGE;
    }
}
