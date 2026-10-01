# Concepts

Everything is edited in the Filament panel and read on the frontend through the
`FilamentCms` facade (`zaheensayyed\FilamentCms\Facades\FilamentCms`, aliased as `FilamentCms`).

| Concept | Panel | Frontend API |
| --- | --- | --- |
| **Pages** | Pages | `FilamentCms::getPage($slug)`, catch-all route |
| **Navigations & items** | Navigations → Items | `FilamentCms::getMenu($key)` |
| **Galleries & images** | Galleries → Images | `FilamentCms::getGallery($slug)` |
| **Settings** | Settings (tabs) | `FilamentCms::setting($key, $default)` |
| **Contact submissions** | Contact Submissions | `<x-filament-cms::contact-form />` |
| **Users & roles** | Users, Roles | panel only, see [roles](roles.md) |

## Pages

A title, a unique-ish `slug`, a rich-text `body` and optional SEO fields (meta title and
description, canonical URL, robots, Open Graph, JSON-LD). See [SEO](seo.md).

## Navigations and items

A **navigation** is one menu (header, footer, …). Look it up by its stable **key**
(`main-menu`); the key is set in the panel and doesn't change when the menu is renamed.

A navigation has **items**, up to **2 levels**: top-level items and their `childItems`.
Each item has a `type` that decides what it links to:

| Type | Links to | `$item->url` |
| --- | --- | --- |
| `page` | a Page (`type_id`) | CMS URL of the item's slug, e.g. `/about` |
| `gallery` | a Gallery (`type_id`) | CMS URL of the item's slug |
| `custom_url` | any URL in `custom_url` | that URL (relative paths are made absolute) |
| `static` | a route your app defines | `url($slug)` |

Child slugs include the parent: a "Team" item under "About" has the slug `about/team`.
Child items can also have an **icon**: any [Heroicon](https://heroicons.com) in any style,
picked from a searchable dropdown in the panel and stored as its Blade name
(`heroicon-o-users`). See [rendering icons](menus.md#child-item-icons).

## Galleries and images

A gallery has a name, slug, description and many images. Images are uploaded in the panel
to Filament's default disk; `$image->image_url` gives the public URL.

## Settings

Grouped key/value settings edited on the Settings page (one tab per group): Company Info,
SEO Defaults and Contact Form. Read any of them with `FilamentCms::setting('group.key')`.
See the [key reference](settings.md).

## Schema

```mermaid
erDiagram
    navigations ||--o{ navigation_items : "has items"
    navigation_items ||--o{ navigation_items : "parent_id (2 levels)"
    navigation_items }o--o| pages : "type = page, type_id"
    navigation_items }o--o| galleries : "type = gallery, type_id"
    galleries ||--o{ gallery_images : "has images"

    navigations {
        bigint id
        string key "unique, e.g. main-menu"
        string name
    }
    navigation_items {
        bigint id
        bigint navigation_id
        bigint parent_id "null for top level"
        string name
        string slug "e.g. about/team"
        int level "1 or 2"
        string type "page, gallery, custom_url, static"
        string type_id
        string custom_url
        string icon "Heroicon name, child items"
    }
    pages {
        bigint id
        string title
        string slug
        longtext body
        string meta_title "plus other SEO columns"
    }
    galleries {
        bigint id
        string name
        string slug "unique"
    }
    gallery_images {
        bigint id
        bigint gallery_id
        string image_name "path on the disk"
    }
    cms_settings {
        string group
        string key
        json value
    }
    contact_form_submissions {
        string name
        string email
        text message
        string mail_status "pending, sent, failed"
    }
```
