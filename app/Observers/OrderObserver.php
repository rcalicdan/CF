<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderHistory;
use App\Enums\OrderStatus;
use App\Jobs\SendSmsJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    public function created(Order $order): void
    {
        OrderHistory::create([
            'order_id' => $order->id,
            'user_id' => Auth::id() ?? 1,
            'old_status' => null,
            'new_status' => $order->status,
            'action_type' => 'created',
            'notes' => 'Zamówienie zostało utworzone',
        ]);
    }

    public function updated(Order $order): void
    {
        $changes = $order->getChanges();
        $original = $order->getOriginal();
        
        if (isset($changes['status'])) {
            $this->logStatusChange($order, $changes, $original);
        }
        
        if (isset($changes['assigned_driver_id'])) {
            $this->logDriverAssignment($order, $changes, $original);
        }
        
        if (isset($changes['schedule_date'])) {
            $this->logScheduleChange($order, $changes, $original);
        }
        
        if (isset($changes['total_amount'])) {
            $this->logAmountChange($order, $changes, $original);
        }
        
        if (isset($changes['client_id'])) {
            $this->logClientChange($order, $changes, $original);
        }
        
        if (isset($changes['is_complaint'])) {
            $this->logComplaintStatusChange($order, $changes, $original);
        }
        
        $filteredChanges = array_filter($changes, function($key) {
            return !in_array($key, [
                'status', 
                'assigned_driver_id', 
                'schedule_date', 
                'total_amount', 
                'client_id', 
                'is_complaint',
                'updated_at'
            ]);
        }, ARRAY_FILTER_USE_KEY);
        
        if (!empty($filteredChanges)) {
            $this->logGeneralUpdate($order, $filteredChanges);
        }
    }

    private function logStatusChange(Order $order, array $changes, array $original): void
    {
        $actionType = match ($changes['status']) {
            OrderStatus::CANCELED->value => 'cancelled',
            OrderStatus::COMPLETED->value => 'completed',
            OrderStatus::DELIVERED->value => 'delivered',
            default => 'status_change',
        };

        $notes = match ($changes['status']) {
            OrderStatus::PENDING->value => 'Status zamówienia zmieniony na: Oczekujące',
            OrderStatus::ACCEPTED->value => 'Zamówienie zostało zaakceptowane',
            OrderStatus::PROCESSING->value => 'Zamówienie jest w trakcie realizacji',
            OrderStatus::COMPLETED->value => 'Zamówienie zostało ukończone',
            OrderStatus::DELIVERED->value => 'Zamówienie zostało dostarczone',
            OrderStatus::UNDELIVERED->value => 'Zamówienie nie mogło zostać dostarczone',
            OrderStatus::CANCELED->value => 'Zamówienie zostało anulowane',
            default => 'Status zamówienia został zaktualizowany',
        };

        OrderHistory::create([
            'order_id' => $order->id,
            'user_id' => Auth::id() ?? 1,
            'old_status' => $original['status'] ?? null,
            'new_status' => $changes['status'],
            'action_type' => $actionType,
            'changes' => ['status' => ['old' => $original['status'] ?? null, 'new' => $changes['status']]],
            'notes' => $notes,
        ]);

        if ($changes['status'] === OrderStatus::DELIVERED->value) {
            $this->sendDeliveryThankYouSms($order);
        }
    }

    /**
     * Send thank you SMS with review link when order is delivered
     */
    private function sendDeliveryThankYouSms(Order $order): void
    {
        $phoneNumber = $order->client?->phone_number;
        
        if (!$phoneNumber) {
            Log::warning('Cannot send delivery SMS: Client has no phone number', [
                'order_id' => $order->id,
                'client_id' => $order->client_id
            ]);
            return;
        }

        $reviewUrl = config('app.url') . '/reviews';
        
        $message = sprintf(
            "Dziękujemy %s! Zamówienie #%d zostało pomyślnie dostarczone. Będziemy wdzięczni za Twoją opinię: %s",
            $order->client->first_name ?? 'szanowny kliencie',
            $order->id,
            $reviewUrl
        );

        SendSmsJob::dispatch($phoneNumber, $message);
        
        Log::info('Delivery thank you SMS queued', [
            'order_id' => $order->id,
            'client_id' => $order->client_id,
            'phone_number' => $phoneNumber,
            'message' => $message
        ]);
    }

    private function logDriverAssignment(Order $order, array $changes, array $original): void
    {
        $oldDriverId = $original['assigned_driver_id'] ?? null;
        $newDriverId = $changes['assigned_driver_id'];
        
        $oldDriver = $oldDriverId ? \App\Models\Driver::with('user')->find($oldDriverId) : null;
        $newDriver = $newDriverId ? \App\Models\Driver::with('user')->find($newDriverId) : null;
        
        $oldDriverName = $oldDriver && $oldDriver->user ? $oldDriver->user->full_name : 'Brak przypisania';
        $newDriverName = $newDriver && $newDriver->user ? $newDriver->user->full_name : 'Brak przypisania';
        
        $notes = $oldDriverId 
            ? "Kierowca zmieniony z '{$oldDriverName}' na '{$newDriverName}'"
            : "Kierowca '{$newDriverName}' został przypisany do zamówienia";

        OrderHistory::create([
            'order_id' => $order->id,
            'user_id' => Auth::id() ?? 1,
            'old_status' => null,
            'new_status' => $order->status,
            'action_type' => 'assigned',
            'changes' => [
                'assigned_driver_id' => [
                    'old' => $oldDriverId,
                    'new' => $newDriverId
                ]
            ],
            'notes' => $notes,
        ]);
    }

    private function logScheduleChange(Order $order, array $changes, array $original): void
    {
        $oldDate = $original['schedule_date'] ?? null;
        $newDate = $changes['schedule_date'];
        
        $oldDateFormatted = $oldDate ? \Carbon\Carbon::parse($oldDate)->format('d.m.Y H:i') : 'Nie ustawiono';
        $newDateFormatted = $newDate ? \Carbon\Carbon::parse($newDate)->format('d.m.Y H:i') : 'Nie ustawiono';
        
        $notes = "Data realizacji zmieniona z '{$oldDateFormatted}' na '{$newDateFormatted}'";

        OrderHistory::create([
            'order_id' => $order->id,
            'user_id' => Auth::id() ?? 1,
            'old_status' => null,
            'new_status' => $order->status,
            'action_type' => 'schedule_updated',
            'changes' => [
                'schedule_date' => [
                    'old' => $oldDate,
                    'new' => $newDate
                ]
            ],
            'notes' => $notes,
        ]);
    }

    private function logAmountChange(Order $order, array $changes, array $original): void
    {
        $oldAmount = $original['total_amount'] ?? 0;
        $newAmount = $changes['total_amount'];
        
        $oldAmountFormatted = number_format((float)$oldAmount, 2, ',', ' ') . ' PLN';
        $newAmountFormatted = number_format((float)$newAmount, 2, ',', ' ') . ' PLN';
        
        $notes = "Kwota całkowita zmieniona z {$oldAmountFormatted} na {$newAmountFormatted}";

        OrderHistory::create([
            'order_id' => $order->id,
            'user_id' => Auth::id() ?? 1,
            'old_status' => null,
            'new_status' => $order->status,
            'action_type' => 'amount_updated',
            'changes' => [
                'total_amount' => [
                    'old' => $oldAmount,
                    'new' => $newAmount
                ]
            ],
            'notes' => $notes,
        ]);
    }

    private function logClientChange(Order $order, array $changes, array $original): void
    {
        $oldClientId = $original['client_id'] ?? null;
        $newClientId = $changes['client_id'];
        
        $oldClient = $oldClientId ? \App\Models\Client::find($oldClientId) : null;
        $newClient = $newClientId ? \App\Models\Client::find($newClientId) : null;
        
        $oldClientName = $oldClient ? $oldClient->full_name : 'Brak klienta';
        $newClientName = $newClient ? $newClient->full_name : 'Brak klienta';
        
        $notes = "Klient zmieniony z '{$oldClientName}' na '{$newClientName}'";

        OrderHistory::create([
            'order_id' => $order->id,
            'user_id' => Auth::id() ?? 1,
            'old_status' => null,
            'new_status' => $order->status,
            'action_type' => 'client_updated',
            'changes' => [
                'client_id' => [
                    'old' => $oldClientId,
                    'new' => $newClientId
                ]
            ],
            'notes' => $notes,
        ]);
    }

    private function logComplaintStatusChange(Order $order, array $changes, array $original): void
    {
        $oldStatus = $original['is_complaint'] ?? false;
        $newStatus = $changes['is_complaint'];
        
        $notes = $newStatus 
            ? 'Zamówienie zostało oznaczone jako reklamacja'
            : 'Status reklamacji został usunięty z zamówienia';

        OrderHistory::create([
            'order_id' => $order->id,
            'user_id' => Auth::id() ?? 1,
            'old_status' => null,
            'new_status' => $order->status,
            'action_type' => 'complaint_updated',
            'changes' => [
                'is_complaint' => [
                    'old' => $oldStatus,
                    'new' => $newStatus
                ]
            ],
            'notes' => $notes,
        ]);
    }

    private function logGeneralUpdate(Order $order, array $changes): void
    {
        OrderHistory::create([
            'order_id' => $order->id,
            'user_id' => Auth::id() ?? 1,
            'old_status' => null,
            'new_status' => $order->status,
            'action_type' => 'updated',
            'changes' => $changes,
            'notes' => 'Szczegóły zamówienia zostały zaktualizowane',
        ]);
    }

    public static function logCarpetAdded(Order $order, $carpetId): void
    {
        OrderHistory::create([
            'order_id' => $order->id,
            'user_id' => Auth::id() ?? 1,
            'old_status' => null,
            'new_status' => $order->status,
            'action_type' => 'carpet_added',
            'changes' => ['carpet_id' => $carpetId],
            'notes' => "Nowy dywan został dodany do zamówienia (ID: {$carpetId})",
        ]);
    }

    public static function logCarpetRemoved(Order $order, $carpetId): void
    {
        OrderHistory::create([
            'order_id' => $order->id,
            'user_id' => Auth::id() ?? 1,
            'old_status' => null,
            'new_status' => $order->status,
            'action_type' => 'carpet_removed',
            'changes' => ['carpet_id' => $carpetId],
            'notes' => "Dywan został usunięty z zamówienia (ID: {$carpetId})",
        ]);
    }

    public static function logServiceAdded(Order $order, $serviceId, $serviceName): void
    {
        OrderHistory::create([
            'order_id' => $order->id,
            'user_id' => Auth::id() ?? 1,
            'old_status' => null,
            'new_status' => $order->status,
            'action_type' => 'service_added',
            'changes' => ['service_id' => $serviceId],
            'notes' => "Usługa '{$serviceName}' została dodana do zamówienia",
        ]);
    }
}