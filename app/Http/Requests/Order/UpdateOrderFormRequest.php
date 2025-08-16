<?php

namespace App\Http\Requests\Order;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderFormRequest extends FormRequest
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
    public function rules()
    {
        return [
            'client_id' => ['sometimes', 'required', Rule::exists('clients', 'id')],
            'assigned_driver_id' => ['nullable', Rule::exists('drivers', 'id')],
            'schedule_date' => ['sometimes', 'nullable', 'date'],
            'price_list_id' => ['sometimes', 'required', Rule::exists('price_lists', 'id')],
            'status' => ['sometimes', 'required', Rule::enum(OrderStatus::class)],
            'is_complaint' => ['required', 'boolean'],
        ];
    }
}
