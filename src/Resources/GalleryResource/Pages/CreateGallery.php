<?php

namespace zaheensayyed\FilamentCms\Resources\GalleryResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use zaheensayyed\FilamentCms\Repositories\GalleryRepository;
use zaheensayyed\FilamentCms\Resources\GalleryResource;
use zaheensayyed\FilamentCms\Traits\CommonResourceTrait;

class CreateGallery extends CreateRecord
{
    use CommonResourceTrait;
    
    protected static string $resource = GalleryResource::class;

    protected function afterCreate(): void
    {
        // Runs after the form fields are saved to the database.
        GalleryRepository::storeImages($this->record, $this->data['images']);
    }
}
