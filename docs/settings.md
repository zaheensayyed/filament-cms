# Settings

Settings are edited in the panel on **Settings** (one tab per group) and read anywhere with:

```php
FilamentCms::setting('company.email');                    // value or null
FilamentCms::setting('company.email', 'info@example.com'); // value or default
FilamentCms::setting('company');                          // the whole group as an array
```

```blade
<a href="mailto:{{ FilamentCms::setting('company.email') }}">
    {{ FilamentCms::setting('company.email') }}
</a>
```

A key that was never saved returns the default, so views work on a fresh install.

## Key reference

| Key | Tab | Value |
| --- | --- | --- |
| `company.contact_no` | Company Info | Contact number |
| `company.email` | Company Info | Email address (also the contact form fallback recipient) |
| `company.address` | Company Info | Address (multi-line) |
| `company.description` | Company Info | Company description |
| `seo.site_name` | SEO Defaults | Used for `og:site_name` and `{site_name}` |
| `seo.title_pattern` | SEO Defaults | e.g. `{title} \| {site_name}` |
| `seo.default_description` | SEO Defaults | Meta description fallback |
| `seo.default_og_image` | SEO Defaults | Path of the default share image on the default disk |
| `seo.default_robots` | SEO Defaults | `index,follow`, `noindex,follow`, `index,nofollow` or `noindex,nofollow` |
| `contact_form.enabled` | Contact Form | `true` / `false` |
| `contact_form.recipients` | Contact Form | Comma-separated emails |
| `contact_form.subject_prefix` | Contact Form | e.g. `[Website]` |
| `contact_form.success_message` | Contact Form | Shown after sending the form |

Multi-line values keep their line breaks; print them with `nl2br(e(...))`:

```blade
<address>{!! nl2br(e(FilamentCms::setting('company.address', ''))) !!}</address>
```

## Caching

All settings are loaded in **one query** and cached forever in one entry; saving the Settings
page clears it, so changes show up on the next request.

> **Performance notes:** `setting()` costs at most **1 query per request** (the first call)
> and **0** once cached, no matter how many keys a layout reads.

## Adding your own tab

Create a group class and register it on the plugin. Its fields are saved and read exactly like
the built-in ones (`FilamentCms::setting('social.facebook')`):

```php
namespace App\Filament\Settings;

use Filament\Forms\Components\TextInput;
use zaheensayyed\FilamentCms\Settings\SettingsGroup;

class SocialLinksGroup extends SettingsGroup
{
    public static function key(): string
    {
        return 'social';
    }

    public static function label(): string
    {
        return 'Social Links';
    }

    public static function schema(): array
    {
        return [
            TextInput::make('facebook')->url(),
            TextInput::make('instagram')->url(),
        ];
    }
}
```

```php
FilamentCmsPlugin::make()->settingsGroups([
    \App\Filament\Settings\SocialLinksGroup::class,
]),
```
