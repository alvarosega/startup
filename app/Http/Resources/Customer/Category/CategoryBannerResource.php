<?php

declare(strict_types=1);

namespace App\Http\Resources\Customer\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryBannerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => (string) data_get($this->resource, 'id'),
            'name'              => (string) data_get($this->resource, 'name'),
            'image_desktop_url' => data_get($this->resource, 'image_path') 
                ? asset('storage/' . data_get($this->resource, 'image_path')) 
                : asset('assets/img/banner_category_placeholder.png'),
            'action_type'       => (string) data_get($this->resource, 'action_type'),
            'target'            => data_get($this->resource, 'target_data'), // SKU o Bundle info
        ];
    }
}