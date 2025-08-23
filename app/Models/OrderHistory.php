<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\ActionService\EnumTranslationService;
use App\Enums\OrderStatus;
use App\Enums\OrderPaymentStatus;

class OrderHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
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

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getOldStatusLabelAttribute(): string
    {
        return $this->old_status ? EnumTranslationService::translate(OrderStatus::from($this->old_status)) : '';
    }

    public function getNewStatusLabelAttribute(): string
    {
        return $this->new_status ? EnumTranslationService::translate(OrderStatus::from($this->new_status)) : '';
    }

    public function getStatusChangeIcon(): string
    {
        if ($this->action_type === 'status_change' && $this->new_status) {
            return match ($this->new_status) {
                OrderStatus::PENDING->value => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                OrderStatus::ACCEPTED->value => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                OrderStatus::PROCESSING->value => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
                OrderStatus::COMPLETED->value => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                OrderStatus::DELIVERED->value => 'M5 8l6 6 10-10',
                OrderStatus::UNDELIVERED->value => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.728-.833-2.498 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z',
                OrderStatus::CANCELED->value => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
                default => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            };
        }

        return match ($this->action_type) {
            'created' => 'M12 4v16m8-8H4',
            'updated' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
            'assigned' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
            'payment_updated' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
            default => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        };
    }

    public function getStatusBadgeColor(): string
    {
        if ($this->action_type === 'status_change' && $this->new_status) {
            return match ($this->new_status) {
                OrderStatus::PENDING->value => 'yellow',
                OrderStatus::ACCEPTED->value => 'blue',
                OrderStatus::PROCESSING->value => 'indigo',
                OrderStatus::COMPLETED->value => 'green',
                OrderStatus::DELIVERED->value => 'emerald',
                OrderStatus::UNDELIVERED->value => 'orange',
                OrderStatus::CANCELED->value => 'red',
                default => 'gray',
            };
        }

        return match ($this->action_type) {
            'created' => 'blue',
            'updated' => 'yellow',
            'assigned' => 'purple',
            'payment_updated' => 'green',
            'cancelled' => 'red',
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
            'status_change' => 'Zmiana statusu',
            'created' => 'Utworzono zamówienie',
            'updated' => 'Zaktualizowano zamówienie',
            'assigned' => 'Przypisano kierowcę',
            'completed' => 'Ukończono zamówienie',
            'cancelled' => 'Anulowano zamówienie',
            'payment_updated' => 'Zaktualizowano płatność',
            'delivered' => 'Dostarczono zamówienie',
            'schedule_updated' => 'Zmieniono termin',
            'amount_updated' => 'Zmieniono kwotę',
            'client_updated' => 'Zmieniono klienta',
            'complaint_updated' => 'Zmieniono status reklamacji',
            'carpet_added' => 'Dodano dywan',
            'carpet_removed' => 'Usunięto dywan',
            'service_added' => 'Dodano usługę',
            default => 'Nieznana akcja',
        };
    }

    private function formatFieldValue(string $field, $value): string
    {
        if (is_null($value)) {
            return 'Nie ustawiono';
        }

        return match ($field) {
            'status' => EnumTranslationService::translate(OrderStatus::from($value)),
            'assigned_driver_id' => $this->getDriverName($value),
            'client_id' => $this->getClientName($value),
            'price_list_id' => $this->getPriceListName($value),
            'is_complaint' => $value ? 'Tak' : 'Nie',
            'total_amount' => number_format((float) $value, 2, ',', ' ') . ' PLN',
            'schedule_date' => $value ? \Carbon\Carbon::parse($value)->format('d.m.Y H:i') : 'Nie ustawiono',
            default => (string) $value,
        };
    }

    private function getFieldDisplayName(string $field): string
    {
        return match ($field) {
            'status' => 'Status',
            'assigned_driver_id' => 'Przypisany kierowca',
            'schedule_date' => 'Data realizacji',
            'total_amount' => 'Kwota całkowita',
            'client_id' => 'Klient',
            'price_list_id' => 'Cennik',
            'is_complaint' => 'Reklamacja',
            'carpet_id' => 'Dywan',
            'service_id' => 'Usługa',
            default => ucfirst(str_replace('_', ' ', $field)),
        };
    }

    private function getDriverName($driverId): string
    {
        if (!$driverId) return 'Nie przypisano';

        $driver = \App\Models\Driver::with('user')->find($driverId);
        return $driver && $driver->user ? $driver->user->full_name : 'Nieznany kierowca';
    }

    private function getClientName($clientId): string
    {
        if (!$clientId) return 'Nie ustawiono';

        $client = \App\Models\Client::find($clientId);
        return $client ? $client->full_name : 'Nieznany klient';
    }

    private function getPriceListName($priceListId): string
    {
        if (!$priceListId) return 'Nie ustawiono';

        $priceList = \App\Models\PriceList::find($priceListId);
        return $priceList ? $priceList->name : 'Nieznany cennik';
    }
}
