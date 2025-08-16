<?php

namespace App\ActionService;

use App\Models\OrderCarpet;
use App\Models\ServicePriceList;
use Illuminate\Support\Facades\DB;

class OrderCarpetMeasurementService
{
    /**
     * Store carpet measurements and update order totals based on carpet services.
     *
     * @return OrderCarpet
     */
    public function storeMeasurement(OrderCarpet $orderCarpet, array $data)
    {
        return DB::transaction(function () use ($orderCarpet, $data) {
            $this->updateCarpetMeasurements($orderCarpet, $data);
            $order = $orderCarpet->order;

            // Calculate total from carpet services
            $totalAmount = $this->calculateOrderTotalFromCarpetServices($order);
            $this->updateOrderTotalAmount($order, $totalAmount);

            return $orderCarpet->load('order.client', 'order.driver.user', 'order.priceList', 'orderCarpetPhotos.user', 'services.priceLists');
        });
    }

    /**
     * Update carpet measurements and adjust area-based service prices.
     */
    private function updateCarpetMeasurements(OrderCarpet $orderCarpet, array $data): void
    {
        $orderCarpet->update([
            'height' => $data['height'],
            'width' => $data['width'],
            'total_area' => $data['height'] * $data['width'],
            'measured_at' => now(),
        ]);

        // Update area-based service prices in the pivot table
        foreach ($orderCarpet->services as $service) {
            if ($service->is_area_based && ! $orderCarpet->order->is_complaint) {
                $price = $this->getServicePrice($service->id, $orderCarpet->order->price_list_id, $service->base_price);
                $newTotal = $price * $orderCarpet->total_area;
                $orderCarpet->services()->updateExistingPivot($service->id, ['total_price' => $newTotal]);
            }
        }
    }

    /**
     * Calculate order total by summing all carpet service prices.
     *
     * @param  \App\Models\Order  $order
     */
    private function calculateOrderTotalFromCarpetServices($order): float
    {
        $totalAmount = 0;
        foreach ($order->orderCarpets as $carpet) {
            foreach ($carpet->services as $service) {
                $totalAmount += $service->pivot->total_price;
            }
        }

        return $totalAmount;
    }

    /**
     * Update the order's total amount.
     *
     * @param  \App\Models\Order  $order
     */
    private function updateOrderTotalAmount($order, float $totalAmount): void
    {
        $order->update(['total_amount' => $totalAmount]);
    }

    /**
     * Retrieve the price of a service from the price list or use the base price.
     *
     * @param  int  $serviceId
     * @param  int  $priceListId
     * @param  float  $basePrice
     */
    private function getServicePrice($serviceId, $priceListId, $basePrice): float
    {
        return ServicePriceList::where('service_id', $serviceId)
            ->where('price_list_id', $priceListId)
            ->value('price') ?? $basePrice;
    }
}
