<?php

namespace zaheensayyed\FilamentCms\Repositories;

use zaheensayyed\FilamentCms\Models\NavigationItem;

class NavigationItemRepository
{
    public static function afterSave(array $data, NavigationItem $record): void
    {

        foreach ($data['child_menu_items'] as $childItems) {

            if (isset($childItems['child_id'])) {
                $navChildItem = NavigationItem::find($childItems['child_id']);
            } else {
                $navChildItem = new NavigationItem();
            }

            $navChildItem->navigation_id = $record->navigation_id;
            $navChildItem->parent_id = $record->id;
            $navChildItem->name = $childItems['child_name'];
            $navChildItem->slug = static::childSlug($record->slug, $childItems['child_slug']);
            $navChildItem->level = 2;
            $navChildItem->type = $childItems['child_type'];
            $navChildItem->type_id = $childItems['child_type_id'];
            $navChildItem->created_by = auth()->id();
            $navChildItem->updated_by = auth()->id();
            $navChildItem->save();
        }

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
