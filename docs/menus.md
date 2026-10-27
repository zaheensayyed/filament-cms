# Rendering menus in Blade

```php
FilamentCms::getMenu('main-menu');
```

Returns the **top-level items** of the menu with key `main-menu` (a collection of
`NavigationItem`), or an empty array when no menu has that key. The menu's display name also
works (`getMenu('Main Menu')`) for older code, but prefer the key: it survives renames.

Each item gives you:

| | |
| --- | --- |
| `$item->title` | Label: the linked page's title / gallery's name, otherwise the item name |
| `$item->url` | Correct `href` for every item type; `null` for `static` items |
| `$item->is_link` | `false` for `static` items (and an empty custom URL): render plain text, not `<a>` |
| `$item->childItems` | Second-level items (always a collection, possibly empty) |
| `$item->type` | `page`, `gallery`, `custom_url` or `static` |
| `$item->linkedPage()` / `linkedGallery()` | The linked model, or `null` for other types |
| `$item->icon` | Heroicon name such as `heroicon-o-home` (child items), or `null` ([see below](#child-item-icons)) |

Always use `$item->url`. Building links yourself (`href="/{{ $item->slug }}"`) is wrong for
custom URL items, whose address is in `custom_url`, and ignores the catch-all route prefix.

The two menus, `main-menu` and `footer-menu`, are created automatically (see
[concepts](concepts.md#navigations-and-items)).

## Copy-paste navbar partial

`resources/views/partials/navbar.blade.php`:

```blade
@php($menu = FilamentCms::getMenu('main-menu'))

<nav class="navbar">
    <ul class="navbar-menu">
        @foreach ($menu as $item)
            @php($isActive = url()->current() === $item->url || $item->childItems->contains(fn ($child) => url()->current() === $child->url))

            <li @class(['navbar-item', 'has-children' => $item->childItems->isNotEmpty(), 'active' => $isActive])>
                @if ($item->is_link)
                    <a href="{{ $item->url }}" @if ($item->type === 'custom_url') target="_blank" rel="noopener" @endif>
                        {{ $item->title }}
                    </a>
                @else
                    <span>{{ $item->title }}</span>
                @endif

                @if ($item->childItems->isNotEmpty())
                    <ul class="navbar-dropdown">
                        @foreach ($item->childItems as $child)
                            <li>
                                @if ($child->is_link)
                                    <a href="{{ $child->url }}" @class(['active' => url()->current() === $child->url])>
                                        @if ($child->icon)
                                            @svg($child->icon, 'navbar-icon')
                                        @endif
                                        {{ $child->title }}
                                    </a>
                                @else
                                    <span>{{ $child->title }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </li>
        @endforeach
    </ul>
</nav>
```

Include it in your layout with `@include('partials.navbar')`.

## Footer columns

In the **Footer Menu**, each top-level item is a column heading and its child items are the
links under it. A `static` heading is plain text:

```blade
<footer class="footer">
    @foreach (FilamentCms::getMenu('footer-menu') as $column)
        <div class="footer-column">
            <h4>
                @if ($column->is_link)
                    <a href="{{ $column->url }}">{{ $column->title }}</a>
                @else
                    {{ $column->title }}
                @endif
            </h4>

            <ul>
                @foreach ($column->childItems as $child)
                    <li>
                        @if ($child->is_link)
                            <a href="{{ $child->url }}">{{ $child->title }}</a>
                        @else
                            {{ $child->title }}
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
</footer>
```

## Child item icons

When adding or editing a menu item in the panel, each child item has an **Icon** dropdown
listing every Heroicon in all four styles: Outline (`heroicon-o-*`), Solid (`heroicon-s-*`),
Mini (`heroicon-m-*`) and Micro (`heroicon-c-*`). Type to search by name and style, e.g.
`arrow solid`; each result shows a preview. The icon is optional.

The chosen name is stored in `$child->icon` and renders with
[Blade Icons](https://github.com/blade-ui-kit/blade-icons), which Filament already installs:

```blade
@if ($child->icon)
    @svg($child->icon, 'h-4 w-4')
    {{-- or: <x-dynamic-component :component="$child->icon" class="h-4 w-4" /> --}}
@endif
```

The SVGs come without a size, so give them one (a class or `width`/`height` in CSS).
The full list is available in PHP as `zaheensayyed\FilamentCms\Support\Heroicons::options()`
(name => label), e.g. to build your own picker.

## Changing where a type links to

`static` items are not links by default (`url` is `null`). To point a type somewhere, e.g.
to make `static` items link to routes your app defines itself, register a resolver in
`AppServiceProvider::boot()`:

```php
use zaheensayyed\FilamentCms\Models\NavigationItem;

NavigationItem::resolveUrlUsing('static', fn (NavigationItem $item) => route($item->slug));
```

## Caching

The whole menu (items, children, linked page titles and slugs) is built once and stored with
`Cache::rememberForever()` in your default cache store. It is rebuilt automatically when a
navigation, navigation item, page or gallery is **saved or deleted** through Eloquent (the panel
always does this), and after every `php artisan migrate` (including `migrate:fresh`).

Items linking to a page or gallery that no longer exists are left out of the menu, together
with their child items, so a deleted page never leaves a link that 404s.

If you change those tables with raw queries or an import script, clear it yourself:

```php
FilamentCms::forgetMenuCache();
```

> **Performance notes:** `getMenu()` costs **2 queries** the first time and **0 queries**
> afterwards until something changes. Reading `title`, `url` and `childItems` fires no
> queries. Menus that don't exist are cached too.
