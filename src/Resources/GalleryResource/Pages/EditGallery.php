<?php

namespace zaheensayyed\FilamentCms\Resources\GalleryResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use zaheensayyed\FilamentCms\Repositories\GalleryRepository;
use zaheensayyed\FilamentCms\Resources\GalleryResource;
use zaheensayyed\FilamentCms\Traits\CommonResourceTrait;

class EditGallery extends EditRecord
{
    use CommonResourceTrait;

    protected static string $resource = GalleryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        GalleryRepository::storeImages($this->record, $this->data['images']);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return GalleryRepository::mutateBeforeFill($data);
    }
}
