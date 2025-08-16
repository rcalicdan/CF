<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDeliveryConfirmationResource extends JsonResource
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
            'confirmation_type' => $this->confirmation_type,
            'confirmation_data' => $this->confirmation_data,
            'signature_url' => $this->signature_url,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
