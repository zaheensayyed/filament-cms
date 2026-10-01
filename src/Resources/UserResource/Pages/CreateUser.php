<?php

namespace zaheensayyed\FilamentCms\Resources\UserResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use zaheensayyed\FilamentCms\Resources\UserResource;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
