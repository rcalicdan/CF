<?php

namespace App\Http\Requests\OrderCarpet;

use App\Enums\OrderCarpetStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderCarpetFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     * This method ensures backward compatibility by normalizing the 'services' input.
     */
    protected function prepareForValidation(): void
    {
        if (empty($this->status)) {
            $this->merge([
                'status' => OrderCarpetStatus::PICKED_UP->value,
            ]);
        }
        
        if ($this->has('services') && \is_array($this->services)) {
            $normalizedServices = [];
            foreach ($this->services as $service) {
                if (is_numeric($service)) {
                    $normalizedServices[] = ['id' => (int)$service, 'quantity' => null];
                } elseif (\is_array($service) && isset($service['id'])) {
                    $normalizedServices[] = $service;
                }
            }
            $this->merge(['services' => $normalizedServices]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'order_id' => ['sometimes', 'required', Rule::exists('orders', 'id')],
            'qr_code' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'required', Rule::enum(OrderCarpetStatus::class)],
            'remarks' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'services' => ['required', 'array'],
            'services.*.id' => ['required', 'integer', Rule::exists('services', 'id')],
            'services.*.quantity' => ['nullable', 'numeric', 'min:0.01', 'max:9999.99'],
        ];
    }
}