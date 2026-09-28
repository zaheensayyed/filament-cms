<?php

namespace zaheensayyed\FilamentCms\Traits;

use zaheensayyed\FilamentCms\Repositories\CommonRepository;

trait CommonResourceTrait
{
    public function mutateFormDataBeforeCreate(array $data): array
    {
        return CommonRepository::mutateDataForCreatedBy($data);
    }

    /**
     * Runs on the Edit page right before the record is updated.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return CommonRepository::mutateDataForUpdatedBy($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
