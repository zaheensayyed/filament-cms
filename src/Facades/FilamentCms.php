<?php

namespace zaheensayyed\FilamentCms\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Database\Eloquent\Collection|array getMenu(string $key)
 * @method static void forgetMenuCache()
 * @method static \zaheensayyed\FilamentCms\Models\Page|null getPage(string $slug)
 * @method static \zaheensayyed\FilamentCms\Models\Gallery|null getGallery(string $slug)
 * @method static \zaheensayyed\FilamentCms\Models\Page|\zaheensayyed\FilamentCms\Models\Gallery|null resolveSlug(string $slug)
 * @method static string url(?string $slug)
 * @method static \Illuminate\Contracts\Pagination\LengthAwarePaginator getPosts(?int $perPage = null)
 * @method static \Illuminate\Database\Eloquent\Collection latestPosts(int $limit = 3)
 * @method static \zaheensayyed\FilamentCms\Models\Post|null getPost(string $slug)
 * @method static string blogUrl()
 * @method static string postUrl(\zaheensayyed\FilamentCms\Models\Post|string $post)
 * @method static \Illuminate\Database\Eloquent\Collection getClients()
 * @method static \Illuminate\Contracts\Pagination\LengthAwarePaginator paginateClients(?int $perPage = null)
 * @method static \Illuminate\Database\Eloquent\Collection featuredClients(?int $limit = null)
 * @method static \zaheensayyed\FilamentCms\Models\Client|null getClient(string $slug)
 * @method static string clientsUrl()
 * @method static mixed setting(string $key, mixed $default = null)
 * @method static array settings()
 * @method static void saveSettings(array $groups)
 * @method static void forgetSettingsCache()
 *
 * @see \zaheensayyed\FilamentCms\FilamentCms
 */
class FilamentCms extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \zaheensayyed\FilamentCms\FilamentCms::class;
    }
}
