<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderCarpetResource extends JsonResource
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
            'qr_code' => $this->qr_code,
            'height' => $this->height,
            'width' => $this->width,
            'total_area' => $this->height && $this->width ? $this->height * $this->width : null,
            'measured_at' => $this->measured_at?->toIso8601String(),
            'status' => $this->status,
            'remarks' => $this->remarks,
            'order_carpet_photos' => OrderCarpetPhotoResource::collection($this->whenLoaded('orderCarpetPhotos')),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'services' => $this->whenLoaded('services', function () {
                $priceListId = $this->price_list_id ?? null;

                return $this->services->map(function ($service) use ($priceListId) {
                    $priceList = $priceListId
                        ? $service->priceLists->firstWhere('id', $priceListId)
                        : null;

                    return [
                        'service_id' => $service->id,
                        'service_name' => $service->name,
                        'service_base_price' => $service->base_price,
                        'service_price_list_price' => $priceList?->pivot->price ?? null,
                        'total_price' => $service->pivot->total_price,
                    ];
                });
            }),
            'order' => new OrderResource($this->whenLoaded('order')),
            'complaint' => new ComplaintResource($this->whenLoaded('complaint')),
        ];
    }
}
