<?php

namespace App\Http\Requests\OrderCarpet;

use App\Enums\OrderCarpetStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderCarpetFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (empty($this->status)) {
            $this->merge([
                'status' => OrderCarpetStatus::PENDING->value,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'order_id' => ['required', Rule::exists('orders', 'id')],
            'qr_code' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(OrderCarpetStatus::class)],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'services' => ['required', 'array'],
            'services.*' => [
                'integer',
                Rule::exists('services', 'id'),
            ],
        ];
    }
}
