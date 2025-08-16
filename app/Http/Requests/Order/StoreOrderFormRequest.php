<?php

namespace App\Http\Requests\Order;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client_id' => ['required', Rule::exists('clients', 'id')],
            'assigned_driver_id' => ['sometimes', 'nullable', Rule::exists('drivers', 'id')],
            'schedule_date' => ['nullable', 'date'],
            'price_list_id' => ['required', Rule::exists('price_lists', 'id')],
            'is_complaint' => ['sometimes', 'boolean'],
            'status' => ['nullable', 'string', Rule::enum(OrderStatus::class)],
        ];
    }
}
