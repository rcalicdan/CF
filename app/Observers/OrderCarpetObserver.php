<?php

namespace App\Observers;

use App\Models\OrderCarpet;
use App\Models\OrderCarpetHistory;
use App\Enums\OrderCarpetStatus;
use Illuminate\Support\Facades\Auth;

class OrderCarpetObserver
{
    public function created(OrderCarpet $orderCarpet): void
    {
        OrderCarpetHistory::create([
            'order_carpet_id' => $orderCarpet->id,
            'user_id' => Auth::id() ?? 1,
            'old_status' => null,
            'new_status' => $orderCarpet->status,
            'action_type' => 'created',
            'notes' => 'Dywan został dodany do zamówienia',
        ]);
    }

    public function updated(OrderCarpet $orderCarpet): void
    {
        $changes = $orderCarpet->getChanges();
        $original = $orderCarpet->getOriginal();
        
        if (isset($changes['status'])) {
            $this->logStatusChange($orderCarpet, $changes, $original);
        }
        
        if (isset($changes['height']) || isset($changes['width']) || isset($changes['total_area']) || isset($changes['measured_at'])) {
            $this->logMeasurementUpdate($orderCarpet, $changes, $original);
        }
        
        if (isset($changes['remarks'])) {
            $this->logRemarksUpdate($orderCarpet, $changes, $original);
        }
        
        $filteredChanges = array_filter($changes, function($key) {
            return !in_array($key, [
                'status', 
                'height', 
                'width', 
                'total_area', 
                'measured_at',
                'remarks',
                'updated_at'
            ]);
        }, ARRAY_FILTER_USE_KEY);
        
        if (!empty($filteredChanges)) {
            $this->logGeneralUpdate($orderCarpet, $filteredChanges);
        }
    }

    private function logStatusChange(OrderCarpet $orderCarpet, array $changes, array $original): void
    {
        $notes = match ($changes['status']) {
            OrderCarpetStatus::PENDING->value => 'Status dywanu zmieniony na: Oczekujący',
            OrderCarpetStatus::PICKED_UP->value => 'Dywan został odebrany',
            OrderCarpetStatus::AT_LAUNDRY->value => 'Dywan jest w pralni',
            OrderCarpetStatus::MEASURED->value => 'Dywan został zmierzony',
            OrderCarpetStatus::COMPLETED->value => 'Obsługa dywanu została ukończona',
            OrderCarpetStatus::WAITING->value => 'Dywan oczekuje na dalsze działania',
            OrderCarpetStatus::DELIVERED->value => 'Dywan został dostarczony',
            OrderCarpetStatus::NOT_DELIVERED->value => 'Dywan nie mógł zostać dostarczony',
            OrderCarpetStatus::RETURNED->value => 'Dywan został zwrócony',
            OrderCarpetStatus::COMPLAINT->value => 'Dywan ma status reklamacji',
            OrderCarpetStatus::UNDER_REVIEW->value => 'Dywan jest w trakcie przeglądu',
            default => 'Status dywanu został zaktualizowany',
        };

        OrderCarpetHistory::create([
            'order_carpet_id' => $orderCarpet->id,
            'user_id' => Auth::id() ?? 1,
            'old_status' => $original['status'] ?? null,
            'new_status' => $changes['status'],
            'action_type' => 'status_change',
            'changes' => ['status' => ['old' => $original['status'] ?? null, 'new' => $changes['status']]],
            'notes' => $notes,
        ]);
    }

    private function logMeasurementUpdate(OrderCarpet $orderCarpet, array $changes, array $original): void
    {
        $measurementChanges = [];
        $notes = 'Pomiary dywanu zostały zaktualizowane: ';
        $updateParts = [];

        if (isset($changes['height'])) {
            $measurementChanges['height'] = [
                'old' => $original['height'] ?? null,
                'new' => $changes['height']
            ];
            $oldHeight = $original['height'] ? number_format($original['height'], 2) . ' cm' : 'nie ustawiono';
            $newHeight = number_format($changes['height'], 2) . ' cm';
            $updateParts[] = "wysokość: {$oldHeight} → {$newHeight}";
        }

        if (isset($changes['width'])) {
            $measurementChanges['width'] = [
                'old' => $original['width'] ?? null,
                'new' => $changes['width']
            ];
            $oldWidth = $original['width'] ? number_format($original['width'], 2) . ' cm' : 'nie ustawiono';
            $newWidth = number_format($changes['width'], 2) . ' cm';
            $updateParts[] = "szerokość: {$oldWidth} → {$newWidth}";
        }

        if (isset($changes['total_area'])) {
            $measurementChanges['total_area'] = [
                'old' => $original['total_area'] ?? null,
                'new' => $changes['total_area']
            ];
            $oldArea = $original['total_area'] ? number_format($original['total_area'], 2) . ' m²' : 'nie obliczono';
            $newArea = number_format($changes['total_area'], 2) . ' m²';
            $updateParts[] = "powierzchnia: {$oldArea} → {$newArea}";
        }

        if (isset($changes['measured_at'])) {
            $measurementChanges['measured_at'] = [
                'old' => $original['measured_at'] ?? null,
                'new' => $changes['measured_at']
            ];
        }

        $notes .= implode(', ', $updateParts);

        OrderCarpetHistory::create([
            'order_carpet_id' => $orderCarpet->id,
            'user_id' => Auth::id() ?? 1,
            'old_status' => null,
            'new_status' => $orderCarpet->status,
            'action_type' => 'measured',
            'changes' => $measurementChanges,
            'notes' => $notes,
        ]);
    }

    private function logRemarksUpdate(OrderCarpet $orderCarpet, array $changes, array $original): void
    {
        $oldRemarks = $original['remarks'] ?? 'Brak uwag';
        $newRemarks = $changes['remarks'] ?? 'Brak uwag';
        
        $notes = "Uwagi zostały zaktualizowane z '{$oldRemarks}' na '{$newRemarks}'";

        OrderCarpetHistory::create([
            'order_carpet_id' => $orderCarpet->id,
            'user_id' => Auth::id() ?? 1,
            'old_status' => null,
            'new_status' => $orderCarpet->status,
            'action_type' => 'remarks_updated',
            'changes' => [
                'remarks' => [
                    'old' => $original['remarks'] ?? null,
                    'new' => $changes['remarks']
                ]
            ],
            'notes' => $notes,
        ]);
    }

    private function logGeneralUpdate(OrderCarpet $orderCarpet, array $changes): void
    {
        OrderCarpetHistory::create([
            'order_carpet_id' => $orderCarpet->id,
            'user_id' => Auth::id() ?? 1,
            'old_status' => null,
            'new_status' => $orderCarpet->status,
            'action_type' => 'updated',
            'changes' => $changes,
            'notes' => 'Szczegóły dywanu zostały zaktualizowane',
        ]);
    }

    public static function logPhotoAdded(OrderCarpet $orderCarpet, int $photoCount = 1): void
    {
        OrderCarpetHistory::create([
            'order_carpet_id' => $orderCarpet->id,
            'user_id' => Auth::id() ?? 1,
            'old_status' => null,
            'new_status' => $orderCarpet->status,
            'action_type' => 'photo_added',
            'changes' => ['photo_count' => $photoCount],
            'notes' => $photoCount === 1 ? 'Dodano nowe zdjęcie' : "Dodano {$photoCount} nowych zdjęć",
        ]);
    }

    public static function logQrGenerated(OrderCarpet $orderCarpet): void
    {
        OrderCarpetHistory::create([
            'order_carpet_id' => $orderCarpet->id,
            'user_id' => Auth::id() ?? 1,
            'old_status' => null,
            'new_status' => $orderCarpet->status,
            'action_type' => 'qr_generated',
            'changes' => ['qr_code' => $orderCarpet->qr_code],
            'notes' => 'Wygenerowano kod QR dla dywanu',
        ]);
    }

    public static function logServiceAdded(OrderCarpet $orderCarpet, $serviceId, string $serviceName): void
    {
        OrderCarpetHistory::create([
            'order_carpet_id' => $orderCarpet->id,
            'user_id' => Auth::id() ?? 1,
            'old_status' => null,
            'new_status' => $orderCarpet->status,
            'action_type' => 'service_added',
            'changes' => ['service_id' => $serviceId],
            'notes' => "Usługa '{$serviceName}' została dodana do dywanu",
        ]);
    }

    public static function logServiceRemoved(OrderCarpet $orderCarpet, $serviceId, string $serviceName): void
    {
        OrderCarpetHistory::create([
            'order_carpet_id' => $orderCarpet->id,
            'user_id' => Auth::id() ?? 1,
            'old_status' => null,
            'new_status' => $orderCarpet->status,
            'action_type' => 'service_removed',
            'changes' => ['service_id' => $serviceId],
            'notes' => "Usługa '{$serviceName}' została usunięta z dywanu",
        ]);
    }

    public static function logComplaintCreated(OrderCarpet $orderCarpet): void
    {
        OrderCarpetHistory::create([
            'order_carpet_id' => $orderCarpet->id,
            'user_id' => Auth::id() ?? 1,
            'old_status' => null,
            'new_status' => $orderCarpet->status,
            'action_type' => 'complaint_created',
            'notes' => 'Utworzono reklamację dla dywanu',
        ]);
    }
}