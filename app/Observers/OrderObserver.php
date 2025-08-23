<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderHistory;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\Auth;

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
            'notes' => 'Order was created',
        ]);
    }

    public function updated(Order $order): void
    {
        $changes = $order->getChanges();
        $original = $order->getOriginal();
        
        if (isset($changes['status'])) {
            $actionType = match ($changes['status']) {
                OrderStatus::CANCELED->value => 'cancelled',
                OrderStatus::COMPLETED->value => 'completed',
                OrderStatus::DELIVERED->value => 'delivered',
                default => 'status_change',
            };

            $notes = match ($changes['status']) {
                OrderStatus::PENDING->value => 'Order is now pending',
                OrderStatus::ACCEPTED->value => 'Order has been accepted',
                OrderStatus::PROCESSING->value => 'Order is being processed',
                OrderStatus::COMPLETED->value => 'Order has been completed',
                OrderStatus::DELIVERED->value => 'Order has been delivered',
                OrderStatus::UNDELIVERED->value => 'Order could not be delivered',
                OrderStatus::CANCELED->value => 'Order has been cancelled',
                default => 'Order status was updated',
            };

            OrderHistory::create([
                'order_id' => $order->id,
                'user_id' => Auth::id() ?? 1,
                'old_status' => $original['status'] ?? null,
                'new_status' => $changes['status'],
                'action_type' => $actionType,
                'changes' => array_filter($changes, fn($key) => $key !== 'updated_at', ARRAY_FILTER_USE_KEY),
                'notes' => $notes,
            ]);
        } elseif (isset($changes['assigned_driver_id'])) {
            OrderHistory::create([
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'old_status' => null,
                'new_status' => $order->status,
                'action_type' => 'assigned',
                'changes' => array_filter($changes, fn($key) => $key !== 'updated_at', ARRAY_FILTER_USE_KEY),
                'notes' => 'Driver assignment was updated',
            ]);
        } elseif (!empty($changes)) {
            $filteredChanges = array_filter($changes, fn($key) => $key !== 'updated_at', ARRAY_FILTER_USE_KEY);
            
            if (!empty($filteredChanges)) {
                OrderHistory::create([
                    'order_id' => $order->id,
                    'user_id' => Auth::id() ?? 1,
                    'old_status' => null,
                    'new_status' => $order->status,
                    'action_type' => 'updated',
                    'changes' => $filteredChanges,
                    'notes' => 'Order details were updated',
                ]);
            }
        }
    }
}