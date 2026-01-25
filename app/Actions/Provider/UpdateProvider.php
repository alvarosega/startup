<?php

namespace App\Actions\Provider;

use App\DTOs\Provider\ProviderData;
use App\Models\Provider;

class UpdateProvider
{
    public function execute(Provider $provider, ProviderData $data): Provider
    {
        $provider->update($data->toArray());
        return $provider->fresh();
    }
}