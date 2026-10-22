# Changelog

All notable changes to `zaheensayyed/filament-cms` are documented in this file.
Merged-but-untagged changes go under **Unreleased**; the README describes the latest tagged release.

## Unreleased

### Added
- Settings page with grouped, extensible tabs and `FilamentCms::setting()` (Company Info tab)
- SEO fields on pages, SEO Defaults settings tab and `<x-filament-cms::seo />` component
- Opt-in catch-all route for pages and galleries, `getPage()`, `getGallery()`, `resolveSlug()`, `url()`
- Stable `key` on navigations; `NavigationItem` `url` accessor for every item type
- Contact form endpoint, `<x-filament-cms::contact-form />`, Contact Form settings tab and
  read-only Contact Submissions resource
- Documentation in `docs/`
- Role-based access with Filament Shield: package policies, Users resource, `admin` and
  `content_manager` roles, `filament-cms:roles` command, Shield setup in `filament-cms:install`
- Docs guard workflow, PR template and README contract (`.github/CONTRIBUTING.md`)
- Icon dropdown for child navigation items with every Heroicon (outline, solid, mini, micro),
  searchable with previews; `navigation_items.icon` column, `$item->icon` and
  `Support\Heroicons::options()`
- Main Menu and Footer Menu are created automatically (config `navigations`); footer columns
  from the Footer Menu's top-level items; `$item->is_link`
- Blog: Blog Posts resource with drafts, scheduled publishing, cover images and SEO fields;
  `blog_posts` table, `*_post` permissions, opt-in `/blog` routes (config `blog`),
  `getPosts()`, `latestPosts()`, `getPost()`, `blogUrl()`, `postUrl()`, `<x-filament-cms::seo :post>`

### Changed
- Navigations can no longer be created or deleted in the panel
- `static` items are plain labels: `$item->url` is `null` (see [UPGRADE.md](UPGRADE.md))
- `getMenu()` builds menus in 2 queries and caches them (see [UPGRADE.md](UPGRADE.md))
- `NavigationItem::page()` / `gallery()` deprecated in favour of `typePage()` / `typeGallery()`
- `GalleryImage::$image_url` is built by the filesystem disk (absolute URL)

### Fixed
- `updated_by` is now stamped on edit
- `filament-cms:install` command is registered and runs the package migrations
- `FilamentCmsTheme` plugin works on Filament 3
- `createdBy` / `updatedBy` relations use the configured user model instead of `App\Models\User`

## 1.0.6

- Last release before the changes above.
