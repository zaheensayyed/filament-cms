# Filament CMS documentation

For Laravel developers wiring the package into a Blade frontend. Read them in order the first
time; each page stands on its own afterwards.

1. [Installation & panel setup](installation.md): requirements, install command, plugin registration
2. [Concepts](concepts.md): pages, navigations and item types, galleries, settings, schema
3. [Rendering menus in Blade](menus.md): `getMenu()`, `url` / `title`, navbar partial, caching
4. [Routing & rendering pages](routing-and-pages.md): catch-all route, `resolveSlug()`, page views
5. [Blog](blog.md): posts, scheduling, `/blog` routes, `getPosts()`
6. [Galleries](galleries.md): `getGallery()`, `image_url`, disks
7. [Clients](clients.md): images, featured clients on the home page, `/clients` routes
8. [SEO](seo.md): `<x-filament-cms::seo />` and the fallback chain
9. [Contact form](contact-form.md): component, endpoint, settings, submission log
10. [Settings](settings.md): `FilamentCms::setting()` and the key reference
11. [Users, roles & permissions](roles.md): Shield, default roles, permission reference
12. [Customization & upgrading](customization.md): publishable files, extension points, upgrades

## Minimal site checklist

- [ ] Package installed, `HasRoles` on the user model, `FilamentCmsPlugin` registered, admin role assigned ([installation](installation.md))
- [ ] `FILAMENT_CMS_ROUTES=true` and a `cms.page` view ([routing](routing-and-pages.md))
- [ ] Layout with `<x-filament-cms::seo />` in `<head>` and the navbar partial ([menus](menus.md), [SEO](seo.md))
- [ ] Contact page with `<x-filament-cms::contact-form />` ([contact form](contact-form.md))
- [ ] Settings filled in: Company Info, SEO Defaults, Contact Form recipients
