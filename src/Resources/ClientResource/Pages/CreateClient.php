<?php

namespace zaheensayyed\FilamentCms\Resources\ClientResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use zaheensayyed\FilamentCms\Models\Client;
use zaheensayyed\FilamentCms\Resources\ClientResource;
use zaheensayyed\FilamentCms\Traits\CommonResourceTrait;

class CreateClient extends CreateRecord
{
    use CommonResourceTrait {
        mutateFormDataBeforeCreate as stampCreatedBy;
    }

    protected static string $resource = ClientResource::class;

    /**
     * New clients go to the end of the list.
     */
    public function mutateFormDataBeforeCreate(array $data): array
    {
        $data['sort_order'] = (int) Client::max('sort_order') + 1;

        return $this->stampCreatedBy($data);
    }
}
