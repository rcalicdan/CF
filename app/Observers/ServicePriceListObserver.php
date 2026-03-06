<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\ServicePriceList;
use Illuminate\Support\Facades\Log;

class ServicePriceListObserver
{
    /**
     * Recalculate carpet service prices for all active orders that use this price list
     * whenever a service price is updated.
     */
    public function updated(ServicePriceList $servicePriceList): void
    {
        $priceListId = $servicePriceList->price_list_id;

        $orders = Order::with('orderCarpets.services')
            ->where('price_list_id', $priceListId)
            ->whereNotIn('status', ['completed', 'cancelled', 'delivered'])
            ->get();

        foreach ($orders as $order) {
            foreach ($order->orderCarpets as $carpet) {
                foreach ($carpet->services as $service) {
                    // Only recalculate the service that changed
                    if ($service->id !== $servicePriceList->service_id) {
                        continue;
                    }

                    if ($order->is_complaint) {
                        $newTotal = 0.0;
                    } else {
                        $price = $servicePriceList->price;
                        $quantity = $service->pivot->quantity;

                        if (! is_null($quantity) && $quantity > 0) {
                            $newTotal = $price * $quantity;
                        } elseif ($service->is_area_based) {
                            $newTotal = $price * ($carpet->total_area ?? 0);
                        } else {
                            $newTotal = $price;
                        }
                    }

                    $carpet->services()->updateExistingPivot($service->id, [
                        'total_price' => $newTotal,
                    ]);
                }
            }

            // Recalculate order total
            $order->load('orderCarpets.services');
            $totalAmount = $order->orderCarpets->sum(function ($c) {
                return $c->services->sum('pivot.total_price');
            });
            $order->update(['total_amount' => $totalAmount]);
        }

        Log::info("ServicePriceListObserver: recalculated {$orders->count()} orders for price_list_id={$priceListId}, service_id={$servicePriceList->service_id}");
    }
}
