<?php

namespace zaheensayyed\FilamentCms\Traits;

trait CommonResourceTrait {

    public function mutateFormDataBeforeCreate(array $data): array
    {    
        $data['created_by'] = auth()->id();
        return $data;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['updated_by'] = auth()->id();
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}