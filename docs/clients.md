# Clients

Add clients in the panel under **Clients**. The website shows them as cards on one page,
`/clients`, and the **featured** ones on the home page. There are no pages per client and the
menu doesn't list clients: link to the clients page with a **Custom URL** item (`/clients`).

## Adding clients

| Field | Notes |
| --- | --- |
| Title | The client's name. |
| Description | Optional, short: shown on the card. |
| Images | Upload several and drag them into order. More than one is shown as a slider on the card. |

| Switch | Effect |
| --- | --- |
| **Featured** | Shows the client on the home page (`FilamentCms::featuredClients()`). Also a toggle on the list. |
| **Published** | On by default. Off hides the client from the clients page and the home page. |

Drag rows on the Clients list to set the order of the cards. New clients go to the end.

**More details** (collapsed) stores optional fields for later: logo, industry, location, website
and rich-text project details. The default view doesn't print them; use them in your own view
when you need them. A `slug` is generated from the title (`acme-towers`, `acme-towers-2`, ...) to
identify the client, e.g. as an HTML `id`; it isn't a URL.

Who can do what is set by the `*_client` permissions ([roles](roles.md#permissions)). Both default
roles can manage clients.

## Frontend route

Turn it on in `.env`:

```dotenv
FILAMENT_CMS_CLIENT_ROUTES=true
```

| URL | Route name | View receives |
| --- | --- | --- |
| `/clients` | `filament-cms.clients.index` | `$clients`: published clients in the panel's order |

It is an ordinary route, so it wins over the catch-all page route: a page, gallery or menu item
with the slug `clients` is no longer reached at `/clients`. Options in `config/filament-cms.php`:

```php
'clients' => [
    'routes' => env('FILAMENT_CMS_CLIENT_ROUTES', false),
    'prefix' => 'clients',           // e.g. "our-clients"
    'middleware' => ['web'],
    'views' => [
        'index' => 'filament-cms::clients.index',
    ],
],
```

The default view is bare HTML. Point `views.index` at your own to use your layout.

## Blade

Three cards per row; more than one image as a slider, one as a single image. Use
`object-fit: contain` (Tailwind `object-contain`) so photos and logos aren't cropped:

```blade
<div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
    @foreach ($clients as $client)
        <article>
            @foreach ($client->image_urls as $url)
                <img src="{{ $url }}" alt="{{ $client->name }}" class="aspect-3/2 w-full object-contain">
            @endforeach

            <h2>{{ $client->name }}</h2>

            @if ($client->description)
                <p>{{ $client->description }}</p>
            @endif
        </article>
    @endforeach
</div>
```

Featured clients on the home page:

```blade
@foreach (FilamentCms::featuredClients(6) as $client)
    <img src="{{ $client->cover_image_url }}" alt="{{ $client->name }}">
    {{ $client->name }}
@endforeach
<a href="{{ FilamentCms::clientsUrl() }}">All clients</a>
```

## API

```php
FilamentCms::getClients();           // published clients, panel order
FilamentCms::featuredClients();      // published + Featured, panel order
FilamentCms::featuredClients(6);     // at most 6
FilamentCms::getClient('acme');      // ?Client by slug, published only
FilamentCms::clientsUrl();           // URL of the clients page (also $client->url)
```

| Attribute | Value |
| --- | --- |
| `name`, `description` | Title and description |
| `image_urls` | Absolute URLs of the images, in order |
| `cover_image_url` | The first image, or the logo when there are none |
| `url` | The clients page |
| `logo_url`, `industry`, `location`, `website_url`, `body` | Optional details |

Query scopes: `Client::published()`, `featured()`, `ordered()`.
