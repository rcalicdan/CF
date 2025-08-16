<?php

namespace App\Http\Requests\ServicePriceList;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServicePriceListFormRequest extends FormRequest
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
            'price_list_id' => ['required', 'string', Rule::exists('price_lists', 'id')],
            'service_id' => ['required', 'string', Rule::exists('services', 'id')],
            'price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
