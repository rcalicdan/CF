<?php

namespace App\ActionService;

use App\Enums\UserRoles;
use App\Models\Order;
use App\Models\OrderCarpet;
use App\Models\Service;
use App\Models\ServicePriceList;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderCarpetService
{
    public function getAllOrderCarpets()
    {
        $user = Auth::user();
        $query = OrderCarpet::with('order', 'orderCarpetPhotos.user', 'order.driver.user', 'order.client', 'order.orderServices', 'order.priceList', 'complaint', 'services.priceLists');

        if ($user->role === UserRoles::DRIVER->value) {
            $query->whereHas(
                'order',
                fn ($orderQuery) => $orderQuery->where('assigned_driver_id', $user->driver->id)
            );
        }

        return $query->when(request('qr_code'), function ($queryOrderCarpet) {
            $queryOrderCarpet->where('qr_code', 'like', '%'.request('qr_code').'%');
        })
            ->when(request('status'), function ($queryOrderCarpet) {
                $queryOrderCarpet->where('status', request('status'));
            })
            ->when(request('order_id'), function ($queryOrderCarpet) {
                $queryOrderCarpet->where('order_id', request('order_id'));
            })
            ->when('width', function ($queryOrderCarpet) {
                $queryOrderCarpet->where('width', request('width'));
            })
            ->when('height', function ($queryOrderCarpet) {
                $queryOrderCarpet->where('height', request('height'));
            })
            ->when('total_area', function ($queryOrderCarpet) {
                $queryOrderCarpet->where('total_area', request('total_area'));
            })
            ->when('measured_at', function ($queryOrderCarpet) {
                $queryOrderCarpet->where('measured_at', request('measured_at'));
            })
            ->paginate(10);
    }

    public function storeOrderCarpet(array $data)
    {
        return DB::transaction(function () use ($data) {
            $order = Order::findOrFail($data['order_id']);
            
            if (isset($data['height']) && isset($data['width']) && 
                is_numeric($data['height']) && is_numeric($data['width'])) {
                $data['total_area'] = round((float)$data['height'] * (float)$data['width'], 2);
            }
            
            $orderCarpet = OrderCarpet::create($data);

            $this->syncServices($orderCarpet, $order, $data['services'] ?? []);

            if (! $order->is_complaint) {
                $this->recalculateOrderTotals($order);
            }

            return $orderCarpet->load(
                'order.client',
                'order.driver.user',
                'order.priceList',
                'complaint',
                'services.priceLists'
            );
        });
    }

    public function updateOrderCarpet(OrderCarpet $orderCarpet, array $data)
    {
        return DB::transaction(function () use ($orderCarpet, $data) {
            $order = $orderCarpet->order;
            
            // Calculate total_area automatically if height and width are provided
            if (isset($data['height']) && isset($data['width']) && 
                is_numeric($data['height']) && is_numeric($data['width'])) {
                $data['total_area'] = round((float)$data['height'] * (float)$data['width'], 2);
            }
            
            $orderCarpet->update($data);

            $this->syncServices($orderCarpet, $order, $data['services'] ?? []);

            if (! $order->is_complaint) {
                $this->recalculateOrderTotals($order);
            }

            return $orderCarpet->load(
                'order.client',
                'orderCarpetPhotos',
                'order.driver.user',
                'order.priceList',
                'services.priceLists'
            );
        });
    }

    public function deleteCarpet(OrderCarpet $orderCarpet)
    {
        DB::transaction(function () use ($orderCarpet) {
            $order = $orderCarpet->order;
            $orderCarpet->delete();

            $order->load('orderCarpets.services');

            $this->recalculateOrderTotals($order);
        });

        return true;
    }

    private function getServicePrice($serviceId, $priceListId, $basePrice): float
    {
        return ServicePriceList::where('service_id', $serviceId)
            ->where('price_list_id', $priceListId)
            ->value('price') ?? $basePrice;
    }

    private function recalculateOrderTotals(Order $order): void
    {
        $totalAmount = 0;

        foreach ($order->orderCarpets as $carpet) {
            foreach ($carpet->services as $service) {
                $totalAmount += $service->pivot->total_price;
            }
        }

        $this->updateOrderAmount($order, $totalAmount);
    }

    private function updateOrderAmount(Order $order, float $totalAmount): void
    {
        $order->update([
            'total_amount' => $totalAmount,
        ]);
    }

    private function syncServices(OrderCarpet $orderCarpet, Order $order, array $serviceIds)
    {
        if (empty($serviceIds)) {
            return;
        }

        $servicesData = array_reduce($serviceIds, function ($result, $serviceId) use ($order, $orderCarpet) {
            $result[$serviceId] = [
                'total_price' => $this->calculateServiceTotal($serviceId, $order, $orderCarpet),
            ];

            return $result;
        }, []);

        $orderCarpet->services()->sync($servicesData);
    }

    private function calculateServiceTotal(int $serviceId, Order $order, OrderCarpet $orderCarpet): float
    {
        $service = Service::findOrFail($serviceId);
        $price = $this->getServicePrice(
            $service->id,
            $order->price_list_id,
            $service->base_price
        );

        if ($order->is_complaint) {
            return 0.0;
        }

        return $service->is_area_based
            ? $price * ($orderCarpet->total_area ?? 0)
            : $price;
    }
}