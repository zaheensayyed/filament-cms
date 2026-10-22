<?php

namespace zaheensayyed\FilamentCms\Seo;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class SeoFields
{
    /**
     * The collapsible SEO section of the page and blog post forms. Every field is optional;
     * blanks fall back to the SEO Defaults settings (see SeoMeta).
     *
     * @param  string  $subject  "page" or "post", used in the helper texts
     */
    public static function section(string $subject = 'page'): Section
    {
        return Section::make('SEO')
            ->description('Optional. Empty fields fall back to the SEO Defaults in Settings.')
            ->collapsed()
            ->schema([
                static::withCharacterCounter(
                    TextInput::make('meta_title')
                        ->label('Meta title')
                        ->helperText("Recommended up to 60 characters. Falls back to the {$subject} title.")
                        ->maxLength(255),
                    60,
                ),
                static::withCharacterCounter(
                    Textarea::make('meta_description')
                        ->label('Meta description')
                        ->helperText($subject === 'post'
                            ? 'Recommended 150–160 characters. Falls back to the excerpt, then the default description.'
                            : 'Recommended 150–160 characters. Falls back to the default description.')
                        ->rows(3)
                        ->maxLength(500),
                    160,
                ),
                Grid::make()
                    ->schema([
                        TextInput::make('canonical_url')
                            ->label('Canonical URL')
                            ->helperText("Falls back to the {$subject}'s own URL.")
                            ->url()
                            ->maxLength(2048),
                        Select::make('robots')
                            ->options(SeoMeta::ROBOTS_OPTIONS)
                            ->placeholder('Default (from settings)'),
                    ])
                    ->columns(2),
                TextInput::make('og_title')
                    ->label('Social share title')
                    ->helperText("og:title. Falls back to the meta title, then the {$subject} title.")
                    ->maxLength(255),
                Textarea::make('og_description')
                    ->label('Social share description')
                    ->helperText('og:description. Falls back to the meta description.')
                    ->rows(3)
                    ->maxLength(500),
                FileUpload::make('og_image')
                    ->label('Social share image')
                    ->helperText($subject === 'post'
                        ? 'Recommended at least 1200×630 px. Also used as twitter:image. Falls back to the cover image, then the default image.'
                        : 'Recommended at least 1200×630 px. Also used as twitter:image. Falls back to the default image.')
                    ->image()
                    ->directory('seo')
                    ->maxSize(2048),
                Textarea::make('structured_data')
                    ->label('Structured data (JSON-LD)')
                    ->helperText('Raw JSON, without the <script> tag.')
                    ->rows(6)
                    ->rule('json'),
            ]);
    }

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
