<?php

namespace zaheensayyed\FilamentCms\Traits;

use zaheensayyed\FilamentCms\Repositories\CommonRepository;

trait CommonResourceTrait
{
    public function mutateFormDataBeforeCreate(array $data): array
    {
        return CommonRepository::mutateDataForCreatedBy($data);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return CommonRepository::mutateDataForUpdatedBy($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
