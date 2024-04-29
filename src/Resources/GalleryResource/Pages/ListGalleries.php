<?php

namespace zaheensayyed\FilamentCms\Resources\GalleryResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use zaheensayyed\FilamentCms\Resources\GalleryResource;

class ListGalleries extends ListRecords
{
    protected static string $resource = GalleryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
