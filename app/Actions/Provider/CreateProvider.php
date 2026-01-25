<?php

namespace App\Actions\Provider;

use App\DTOs\Provider\ProviderData;
use App\Models\Provider;

class CreateProvider
{
    public function execute(ProviderData $data): Provider
    {
        return Provider::create($data->toArray());
    }
}