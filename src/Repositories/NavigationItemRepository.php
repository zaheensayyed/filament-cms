<?php

namespace zaheensayyed\FilamentCms\Repositories;

use zaheensayyed\FilamentCms\Models\NavigationItem;

class NavigationItemRepository
{
    /**
     * Creates, updates and deletes the child items of $record to match the form's
     * "child_menu_items" repeater: a child removed from the repeater is deleted.
     */
    public static function afterSave(array $data, NavigationItem $record): void
    {
        $keptIds = [];

        foreach ($data['child_menu_items'] ?? [] as $childItems) {

            if (isset($childItems['child_id'])) {
                $navChildItem = NavigationItem::find($childItems['child_id']);
            } else {
                $navChildItem = new NavigationItem;
                $navChildItem->created_by = auth()->id();
            }

            $navChildItem->navigation_id = $record->navigation_id;
            $navChildItem->parent_id = $record->id;
            $navChildItem->name = $childItems['child_name'];
            $navChildItem->slug = static::childSlug($record->slug, $childItems['child_slug']);
            $navChildItem->level = 2;
            $navChildItem->type = $childItems['child_type'];
            // A custom URL child has a URL and no page / gallery, every other type the reverse.
            $isCustomUrl = ! NavigationItem::hasOptions($childItems['child_type']);
            $navChildItem->type_id = $isCustomUrl ? null : ($childItems['child_type_id'] ?? null);
            $navChildItem->custom_url = $isCustomUrl ? ($childItems['child_custom_url'] ?? null) : null;
            $navChildItem->icon = $childItems['child_icon'] ?? null;
            $navChildItem->updated_by = auth()->id();
            $navChildItem->save();

            $keptIds[] = $navChildItem->id;
        }

        // One by one, so the model events run (menu cache, deeper children).
        NavigationItem::query()
            ->where('parent_id', $record->id)
            ->whereNotIn('id', $keptIds)
            ->get()
            ->each
            ->delete();
    }

    public static function mutateBeforeFill(array $data, NavigationItem $record): array
    {
        $data['child_menu_items'] = [];

        foreach ($record->childItems as $childItem) {
            $item = [];

            $item['child_name'] = $childItem->name;
            $item['child_slug'] = $childItem->slug;
            $item['child_type'] = $childItem->type;
            $item['child_type_id'] = $childItem->type_id;
            $item['child_custom_url'] = $childItem->custom_url;
            $item['child_icon'] = $childItem->icon;
            $item['child_id'] = $childItem->id;

            $data['child_menu_items'][$childItem->id] = $item;
        }

        return $data;
    }

    public static function childSlug($parent_slug, $slug)
    {
        $slugArr = explode('/', $slug);

        return implode('/', [$parent_slug, end($slugArr)]);
    }
}
