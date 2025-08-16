<?php

namespace App\ActionService;

use App\Enums\OrderCarpetStatus;
use App\Jobs\SendSmsJob;
use App\Models\OrderCarpet;

class QrValidationService
{
    public function validateAndUpdateStatus(string $qrCode, OrderCarpetStatus $status, string $message = ''): OrderCarpet
    {
        $orderCarpet = OrderCarpet::with('order.client')->where('qr_code', $qrCode)->firstOrFail();

        $orderCarpet->update([
            'status' => $status->value,
        ]);

        $testPhoneNumber = '48793676408'; // this just for testing purposes for sending test message for the number you provided, remove this after setting the app to production.
        $clientPhoneNumber = preg_replace('/[^\d]/', '', $orderCarpet->order->client->phone_number); // use this in the job dispatch for sending real sms message to clients

        if (! empty($message)) {
            SendSmsJob::dispatch($clientPhoneNumber, $message);
        }

        return $orderCarpet;
    }

    public function validateIfLaundryWasCleaned(array $data): bool|string
    {
        $orderCarpet = OrderCarpet::where('qr_code', $data['qr_code'])->firstOrFail();

        if ($orderCarpet->status !== 'Completed') {
            return false;
        }

        return true;
    }
}
