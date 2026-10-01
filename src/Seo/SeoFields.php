<?php

namespace zaheensayyed\FilamentCms\Seo;

use Filament\Forms\Components\Field;

class SeoFields
{
    /**
     * Shows a live "42 / 60 characters" hint that turns red past the recommended length.
     *
     * @template T of Field
     *
     * @param  T  $field
     * @return T
     */
    public static function withCharacterCounter(Field $field, int $recommended): Field
    {
        return $field
            ->live(debounce: 300)
            ->hint(fn (?string $state): string => mb_strlen((string) $state) . " / {$recommended} characters")
            ->hintColor(fn (?string $state): string => mb_strlen((string) $state) > $recommended ? 'danger' : 'gray');
    }
}
