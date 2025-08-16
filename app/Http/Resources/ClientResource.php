<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'street_name' => $this->street_name,
            'street_number' => $this->street_number,
            'postal_code' => preg_replace('/^(\d{2})(\d{3})$/', '$1-$2', str_pad($this->postal_code, 5, '0', STR_PAD_LEFT)),
            'phone_number' => $this->phone_number,
            'city' => $this->city,
            'remarks' => $this->remarks,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
