<?php

namespace App\Http\Requests\OrderDeliveryConfirmation;

use App\Enums\OrderDeliveryConfirmationType;
use App\Enums\OrderPaymentMethods;
use App\Enums\OrderPaymentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeliveryConfirmationFormRequest extends FormRequest
{
    public function authorize()
    {
        // // Ensure the authenticated user is a driver and is assigned to this order
        // return $this->user()->role === 'Driver' &&
        //        $this->route('order_id') &&
        //        $this->user()->driver->orders()->where('id', $this->route('order_id'))->exists();

        return true;
    }

    public function rules()
    {
        return [
            'confirmation_type' => ['required', Rule::enum(OrderDeliveryConfirmationType::class)],

            // Conditional validation based on confirmation_type
            'signature_image' => ['required_if:confirmation_type,signature', 'image', 'max:10240'],
            'confirmation_data' => ['required_if:confirmation_type,data', 'string'],

            // Payment details validation
            'payment_details' => ['required', 'array'],
            'payment_details.payment_method' => ['required', Rule::enum(OrderPaymentMethods::class)],
            'payment_details.status' => ['required', Rule::enum(OrderPaymentStatus::class)],
        ];
    }

    public function messages()
    {
        return [
            'confirmation_type.required' => 'Please specify the confirmation type.',
            'confirmation_type.in' => 'The confirmation type must be either signature or data.',
            'signature_url.required_if' => 'A signature URL is required when confirmation type is signature.',
            'confirmation_data.required_if' => 'Confirmation data is required when confirmation type is data.',
            'payment_details.required' => 'Payment details are required.',
            'payment_details.payment_method.required' => 'Payment method is required.',
            'payment_details.payment_method.in' => 'Payment method must be either Cash or Card.',
            'payment_details.status.required' => 'Payment status is required.',
            'payment_details.status.in' => 'Invalid payment status provided.',
        ];
    }

    protected function prepareForValidation()
    {
        // Convert amount to cents/smallest currency unit if needed
        if (isset($this->payment_details['amount'])) {
            $this->merge([
                'payment_details' => array_merge(
                    $this->payment_details,
                    ['amount' => (float) $this->payment_details['amount']]
                ),
            ]);
        }
    }
}
