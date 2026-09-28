<?php

namespace zaheensayyed\FilamentCms\Policies;

use zaheensayyed\FilamentCms\Resources\ContactSubmissionResource;

class ContactFormSubmissionPolicy extends ResourcePolicy
{
    protected function resource(): string
    {
        return ContactSubmissionResource::class;
    }
}
