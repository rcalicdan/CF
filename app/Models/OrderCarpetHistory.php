<?php
// app/Models/OrderCarpetHistory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\ActionService\EnumTranslationService;
use App\Enums\OrderCarpetStatus;

class OrderCarpetHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_carpet_id',
        'user_id',
        'old_status',
        'new_status',
        'notes',
        'action_type',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function orderCarpet()
    {
        return $this->belongsTo(OrderCarpet::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getOldStatusLabelAttribute(): string
    {
        return $this->old_status ? EnumTranslationService::translate(OrderCarpetStatus::from($this->old_status)) : '';
    }

    public function getNewStatusLabelAttribute(): string
    {
        return $this->new_status ? EnumTranslationService::translate(OrderCarpetStatus::from($this->new_status)) : '';
    }

    public function getStatusChangeIcon(): string
    {
        if ($this->action_type === 'status_change' && $this->new_status) {
            return match ($this->new_status) {
                OrderCarpetStatus::PENDING->value => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                OrderCarpetStatus::PICKED_UP->value => 'M5 13l4 4L19 7',
                OrderCarpetStatus::AT_LAUNDRY->value => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547A1.934 1.934 0 004 17.934V18a2 2 0 002 2h12a2 2 0 002-2v-.066a1.934 1.934 0 00-.244-1.066z',
                OrderCarpetStatus::MEASURED->value => 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z',
                OrderCarpetStatus::COMPLETED->value => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                OrderCarpetStatus::WAITING->value => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                OrderCarpetStatus::DELIVERED->value => 'M5 8l6 6 10-10',
                OrderCarpetStatus::NOT_DELIVERED->value => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.728-.833-2.498 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z',
                OrderCarpetStatus::RETURNED->value => 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6',
                OrderCarpetStatus::COMPLAINT->value => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.728-.833-2.498 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z',
                OrderCarpetStatus::UNDER_REVIEW->value => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                default => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            };
        }

        return match ($this->action_type) {
            'created' => 'M12 4v16m8-8H4',
            'updated' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
            'measured' => 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z',
            'photo_added' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
            'qr_generated' => 'M3 4a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 13a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H4a1 1 0 01-1-1v-3zM13 4a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1V4zM9 9h1m4 0h1m-4 4h1m0 4h1m4-8h1m0 4h1',
            'service_added' => 'M12 4v16m8-8H4',
            'service_removed' => 'M20 12H4',
            'complaint_created' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.728-.833-2.498 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z',
            default => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        };
    }

    public function getStatusBadgeColor(): string
    {
        if ($this->action_type === 'status_change' && $this->new_status) {
            return match ($this->new_status) {
                OrderCarpetStatus::PENDING->value => 'yellow',
                OrderCarpetStatus::PICKED_UP->value => 'blue',
                OrderCarpetStatus::AT_LAUNDRY->value => 'indigo',
                OrderCarpetStatus::MEASURED->value => 'purple',
                OrderCarpetStatus::COMPLETED->value => 'green',
                OrderCarpetStatus::WAITING->value => 'orange',
                OrderCarpetStatus::DELIVERED->value => 'emerald',
                OrderCarpetStatus::NOT_DELIVERED->value => 'red',
                OrderCarpetStatus::RETURNED->value => 'gray',
                OrderCarpetStatus::COMPLAINT->value => 'red',
                OrderCarpetStatus::UNDER_REVIEW->value => 'amber',
                default => 'gray',
            };
        }

        return match ($this->action_type) {
            'created' => 'blue',
            'updated' => 'yellow',
            'measured' => 'purple',
            'photo_added' => 'green',
            'qr_generated' => 'indigo',
            'service_added' => 'blue',
            'service_removed' => 'red',
            'complaint_created' => 'red',
            default => 'gray',
        };
    }

    public function getFormattedChangesAttribute(): array
    {
        if (!$this->changes || !is_array($this->changes)) {
            return [];
        }

        $formatted = [];
        foreach ($this->changes as $field => $change) {
            $fieldName = $this->getFieldDisplayName($field);

            if (is_array($change) && isset($change['old'], $change['new'])) {
                $formatted[$fieldName] = [
                    'old' => $this->formatFieldValue($field, $change['old']),
                    'new' => $this->formatFieldValue($field, $change['new']),
                ];
            } else {
                $formatted[$fieldName] = $this->formatFieldValue($field, $change);
            }
        }

        return $formatted;
    }

    public function getActionTypeLabel(): string
    {
        return match ($this->action_type) {
            'status_change' => 'Zmiana statusu dywanu',
            'created' => 'Utworzono dywan',
            'updated' => 'Zaktualizowano dywan',
            'measured' => 'Przeprowadzono pomiar',
            'photo_added' => 'Dodano zdjęcie',
            'qr_generated' => 'Wygenerowano kod QR',
            'service_added' => 'Dodano usługę',
            'service_removed' => 'Usunięto usługę',
            'complaint_created' => 'Utworzono reklamację',
            'complaint_resolved' => 'Rozwiązano reklamację',
            'dimensions_updated' => 'Zmieniono wymiary',
            'remarks_updated' => 'Zmieniono uwagi',
            default => 'Nieznana akcja',
        };
    }

    private function formatFieldValue(string $field, $value): string
    {
        if (is_null($value)) {
            return 'Nie ustawiono';
        }

        return match ($field) {
            'status' => EnumTranslationService::translate(OrderCarpetStatus::from($value)),
            'height' => $value ? number_format((float) $value, 2, ',', ' ') . ' cm' : 'Nie zmierzono',
            'width' => $value ? number_format((float) $value, 2, ',', ' ') . ' cm' : 'Nie zmierzono',
            'total_area' => $value ? number_format((float) $value, 2, ',', ' ') . ' m²' : 'Nie obliczono',
            'measured_at' => $value ? \Carbon\Carbon::parse($value)->format('d.m.Y H:i') : 'Nie ustawiono',
            'service_id' => $this->getServiceName($value),
            'photo_count' => $value . ' zdjęć',
            default => (string) $value,
        };
    }

    private function getFieldDisplayName(string $field): string
    {
        return match ($field) {
            'status' => 'Status',
            'height' => 'Wysokość',
            'width' => 'Szerokość',
            'total_area' => 'Powierzchnia',
            'measured_at' => 'Data pomiaru',
            'remarks' => 'Uwagi',
            'service_id' => 'Usługa',
            'photo_count' => 'Liczba zdjęć',
            default => ucfirst(str_replace('_', ' ', $field)),
        };
    }

    private function getServiceName($serviceId): string
    {
        if (!$serviceId) return 'Nieznana usługa';

        $service = \App\Models\Service::find($serviceId);
        return $service ? $service->name : 'Nieznana usługa';
    }
}