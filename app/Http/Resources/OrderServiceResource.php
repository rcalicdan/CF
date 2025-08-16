<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderServiceResource extends JsonResource
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
            'order_id' => $this->order_id,
            'service_id' => $this->service_id,
            'service_name' => $this->whenLoaded('service', fn () => $this->service->name),
            'service_base_price' => $this->whenLoaded('service', fn () => $this->service->base_price),
            'is_service_by_area' => $this->whenLoaded('service', fn () => $this->service->is_area_based),
            'total_price' => $this->total_price,
        ];
    }
}
