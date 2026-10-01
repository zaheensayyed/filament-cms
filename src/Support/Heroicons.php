<?php

namespace zaheensayyed\FilamentCms\Support;

use Composer\InstalledVersions;
use Illuminate\Support\Str;

/**
 * Every Heroicon shipped with blade-ui-kit/blade-heroicons (a Filament dependency), in all
 * four styles, as Blade icon names such as "heroicon-o-home". Read from the package's SVG
 * folder, so icons added by a blade-heroicons update show up without code changes.
 */
class Heroicons
{
    public const PREFIX = 'heroicon';

    /**
     * Style prefix => label, in the order they are listed.
     */
    public const STYLES = [
        'o' => 'Outline',
        's' => 'Solid',
        'm' => 'Mini',
        'c' => 'Micro',
    ];

    /**
     * @var array<string, string>|null
     */
    protected static ?array $icons = null;

    /**
     * @return array<string, string> icon name => label, e.g. "heroicon-o-home" => "Home (Outline)"
     */
    public static function options(): array
    {
        if (static::$icons !== null) {
            return static::$icons;
        }

        $icons = [];

        foreach (glob(static::path() . '/*.svg') ?: [] as $file) {
            [$style, $name] = explode('-', basename($file, '.svg'), 2) + [1 => null];

            if ($name === null || ! isset(static::STYLES[$style])) {
                continue;
            }

            $icons[static::PREFIX . "-{$style}-{$name}"] = [$name, $style];
        }

        $styleOrder = array_flip(array_keys(static::STYLES));

        uasort($icons, fn (array $a, array $b) => [$a[0], $styleOrder[$a[1]]] <=> [$b[0], $styleOrder[$b[1]]]);

        return static::$icons = array_map(
            fn (array $icon) => Str::headline($icon[0]) . ' (' . static::STYLES[$icon[1]] . ')',
            $icons,
        );
    }

    /**
     * @return array<string>
     */
    public static function names(): array
    {
        return array_keys(static::options());
    }

    public static function exists(?string $icon): bool
    {
        return $icon !== null && isset(static::options()[$icon]);
    }

    /**
     * Icons whose name or label contains every word of $search; all icons when $search is blank.
     *
     * @return array<string, string>
     */
    public static function search(?string $search, ?int $limit = null): array
    {
        $words = array_filter(explode(' ', Str::lower(trim((string) $search))));

        $results = array_filter(static::options(), function (string $label, string $icon) use ($words) {
            $haystack = Str::lower("{$icon} {$label}");

            foreach ($words as $word) {
                if (! str_contains($haystack, $word)) {
                    return false;
                }
            }

            return true;
        }, ARRAY_FILTER_USE_BOTH);

        return $limit === null ? $results : array_slice($results, 0, $limit, true);
    }

    /**
     * Label with an inline SVG preview, for selects that allow HTML.
     */
    public static function previewLabel(?string $icon): ?string
    {
        if (! static::exists($icon)) {
            return null;
        }

        $svg = svg($icon, 'h-5 w-5 shrink-0', ['style' => 'width: 1.25rem; height: 1.25rem'])->toHtml();

        return '<span class="flex items-center gap-2" style="display: flex; align-items: center; gap: 0.5rem">'
            . $svg . '<span>' . e(static::options()[$icon]) . '</span></span>';
    }

    /**
     * @param  array<string, string>  $options
     * @return array<string, string>
     */
    public static function withPreviews(array $options): array
    {
        foreach ($options as $icon => $label) {
            $options[$icon] = static::previewLabel($icon) ?? e($label);
        }

        return $options;
    }

    public static function path(): string
    {
        $base = InstalledVersions::isInstalled('blade-ui-kit/blade-heroicons')
            ? InstalledVersions::getInstallPath('blade-ui-kit/blade-heroicons')
            : null;

        return rtrim($base ?? base_path('vendor/blade-ui-kit/blade-heroicons'), '/\\') . '/resources/svg';
    }

    /**
     * @internal Resets the in-memory list (tests).
     */
    public static function flush(): void
    {
        static::$icons = null;
    }
}
