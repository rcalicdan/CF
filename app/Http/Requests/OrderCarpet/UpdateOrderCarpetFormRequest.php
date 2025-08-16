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

    protected function prepareForValidation(): void
    {
        if (empty($this->status)) {
            $this->merge([
                'status' => OrderCarpetStatus::PICKED_UP->value,
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
            'order_id' => ['sometimes', 'required', Rule::exists('orders', 'id')],
            'qr_code' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'required', Rule::enum(OrderCarpetStatus::class)],
            'remarks' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'services' => ['required', 'array'],
            'services.*' => [
                'integer',
                Rule::exists('services', 'id'),
            ],
        ];
    }
}
