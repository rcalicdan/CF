<?php

namespace App\ActionService;

use App\Enums\OrderCarpetStatus;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderDeliveryConfirmation;
use App\Models\OrderPayment;
use Illuminate\Support\Facades\DB;

class OrderDeliveryService
{
    public function confirmDelivery(Order $order, array $validatedData)
    {
        return DB::transaction(function () use ($order, $validatedData) {
            $deliveryConfirmation = $this->createDeliveryConfirmation($order, $validatedData);
            $payment = $this->createPayment($order, $validatedData['payment_details']);

            $this->updateOrderStatus($order);

            return [
                'delivery_confirmation' => $deliveryConfirmation,
                'payment' => $payment,
                'order' => $order->fresh(),
            ];
        });
    }

    private function createDeliveryConfirmation(Order $order, array $validatedData): OrderDeliveryConfirmation
    {
        $confirmationData = [
            'order_id' => $order->id,
            'confirmation_type' => $validatedData['confirmation_type'],
        ];

        if ($validatedData['confirmation_type'] === 'signature') {
            $confirmationData['signature_url'] = $validatedData['signature_url'];
        } else {
            $confirmationData['confirmation_data'] = $validatedData['confirmation_data'];
        }

        // Changed from orderDeliveryConfirmation->create to orderDeliveryConfirmation()
        return $order->orderDeliveryConfirmation()->create($confirmationData);
    }

    private function createPayment(Order $order, array $paymentDetails): OrderPayment
    {
        return $order->orderPayment()->create([
            'amount_paid' => $order->total_amount,
            'payment_method' => $paymentDetails['payment_method'],
            'status' => $paymentDetails['status'],
            'paid_at' => now(),
        ]);
    }

    private function updateOrderStatus(Order $order): void
    {
        \DB::transaction(function () use ($order) {
            $order->orderCarpets()->update([
                'status' => OrderCarpetStatus::DELIVERED->value,
            ]);

            $order->update([
                'status' => OrderStatus::COMPLETED->value,
            ]);
        });
    }
}
