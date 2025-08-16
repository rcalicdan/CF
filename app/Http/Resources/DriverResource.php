<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
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
            'first_name' => $this->whenLoaded('user', fn () => $this->user->first_name),
            'last_name' => $this->whenLoaded('user', fn () => $this->user->last_name),
            'license_number' => $this->license_number,
            'vehicle_details' => $this->vehicle_details,
            'phone_number' => $this->phone_number,
        ];
    }
}
