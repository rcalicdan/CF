<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'client' => ClientResource::make($this->whenLoaded('client')),
            'driver' => DriverResource::make($this->whenLoaded('driver')),
            'schedule_date' => $this->schedule_date?->toIso8601String(),
            'price_list' => PriceListResource::make($this->whenLoaded('priceList')),
            'status' => $this->status,
            'is_complaint' => $this->is_complaint,
            'total_amount' => $this->total_amount,
            'order_carpets' => OrderCarpetResource::collection($this->whenLoaded('orderCarpets')),
            'order_service' => OrderServiceResource::collection($this->whenLoaded('orderServices')),
            'order_delivery_confirmation' => new OrderDeliveryConfirmationResource($this->whenLoaded('orderDeliveryConfirmation')),
            'order_payment' => new OrderPaymentResource($this->whenLoaded('orderPayment')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
