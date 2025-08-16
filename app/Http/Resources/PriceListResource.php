<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'location_postal_code' => $this->location_postal_code,
            'price_list_services' => $this->whenLoaded('services', function () {
                return $this->services->map(function ($service) {
                    return [
                        'service_price_list_id' => $service->pivot->id,
                        'service_id' => $service->id,
                        'service_name' => $service->name,
                        'service_base_price' => $service->base_price,
                        'price' => $service->pivot->price,
                    ];
                });
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
