<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderCarpetPhotoResource extends JsonResource
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
            'order_carpet_id' => $this->order_carpet_id,
            'photo_url' => $this->photo_path,
            'taken_by' => $this->whenLoaded('user', $this->user?->full_name),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
