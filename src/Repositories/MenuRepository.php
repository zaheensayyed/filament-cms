<?php

namespace zaheensayyed\FilamentCms\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\JoinClause;
use zaheensayyed\FilamentCms\Models\Gallery;
use zaheensayyed\FilamentCms\Models\Navigation;
use zaheensayyed\FilamentCms\Models\NavigationItem;
use zaheensayyed\FilamentCms\Models\Page;

class MenuRepository
{
    /**
     * Builds a menu in two queries: one for the navigation, one for all of its items
     * joined with their linked page/gallery. Returns the top-level items with
     * childItems, typePage and typeGallery already set, so rendering fires no queries.
     * Items linking to a page or gallery that no longer exists are left out, with their
     * children. Returns null when no navigation matches.
     *
     * @return Collection<int, NavigationItem>|null
     */
    public static function build(string $key): ?Collection
    {
        $navigation = static::findNavigation($key);

        if (! $navigation) {
            return null;
        }

        $items = NavigationItem::query()
            ->select('navigation_items.*')
            ->addSelect([
                'pages.id as linked_page_id',
                'pages.title as linked_page_title',
                'pages.slug as linked_page_slug',
                'galleries.id as linked_gallery_id',
                'galleries.name as linked_gallery_name',
                'galleries.slug as linked_gallery_slug',
            ])
            ->leftJoin('pages', function (JoinClause $join) {
                $join->on('pages.id', '=', 'navigation_items.type_id')
                    ->where('navigation_items.type', NavigationItem::TYPE_PAGE);
            })
            ->leftJoin('galleries', function (JoinClause $join) {
                $join->on('galleries.id', '=', 'navigation_items.type_id')
                    ->where('navigation_items.type', NavigationItem::TYPE_GALLERY);
            })
            ->where('navigation_items.navigation_id', $navigation->id)
            ->orderBy('navigation_items.id')
            ->get()
            ->each(fn (NavigationItem $item) => static::hydrateLinkTargets($item))
            ->reject(fn (NavigationItem $item) => static::hasMissingTarget($item));

        $children = $items->where('level', '>', 1)->groupBy('parent_id');

        return $items
            ->where('level', 1)
            ->values()
            ->each(function (NavigationItem $item) use ($children) {
                $childItems = new Collection($children->get($item->id)?->values()->all() ?? []);

                $childItems->each->setRelation('childItems', new Collection);

                $item->setRelation('childItems', $childItems);
            });
    }

    /**
     * Stable key first; the display name still works for backward compatibility.
     */
    public static function findNavigation(string $key): ?Navigation
    {
        return Navigation::query()
            ->where('key', $key)
            ->orWhere('name', $key)
            ->get()
            ->sortByDesc(fn (Navigation $navigation) => $navigation->key === $key)
            ->first();
    }

    /**
     * A page or gallery item whose target was deleted outside the panel (e.g. directly in the
     * database), so its link would 404.
     */
    protected static function hasMissingTarget(NavigationItem $item): bool
    {
        return match ($item->type) {
            NavigationItem::TYPE_PAGE => $item->typePage === null,
            NavigationItem::TYPE_GALLERY => $item->typeGallery === null,
            default => false,
        };
    }

    protected static function hydrateLinkTargets(NavigationItem $item): void
    {
        $attributes = $item->getAttributes();

        $page = $attributes['linked_page_id'] === null ? null : (new Page)->newFromBuilder([
            'id' => $attributes['linked_page_id'],
            'title' => $attributes['linked_page_title'],
            'slug' => $attributes['linked_page_slug'],
        ]);

        $gallery = $attributes['linked_gallery_id'] === null ? null : (new Gallery)->newFromBuilder([
            'id' => $attributes['linked_gallery_id'],
            'name' => $attributes['linked_gallery_name'],
            'slug' => $attributes['linked_gallery_slug'],
        ]);

        $item->setRawAttributes(array_diff_key($attributes, array_flip([
            'linked_page_id', 'linked_page_title', 'linked_page_slug',
            'linked_gallery_id', 'linked_gallery_name', 'linked_gallery_slug',
        ])), true);

        $item->setRelation('typePage', $page);
        $item->setRelation('typeGallery', $gallery);
    }
}
