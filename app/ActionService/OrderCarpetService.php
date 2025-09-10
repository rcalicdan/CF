<?php
// app/ActionService/OrderCarpetService.php

namespace App\ActionService;

use App\Enums\UserRoles;
use App\Models\Order;
use App\Models\OrderCarpet;
use App\Models\Service;
use App\Models\ServicePriceList;
use App\Observers\OrderCarpetObserver;
use App\Observers\OrderObserver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderCarpetService
{
    public function getAllOrderCarpets()
    {
        $user = Auth::user();
        $query = OrderCarpet::with([
            'order',
            'orderCarpetPhotos.user',
            'order.driver.user',
            'order.client',
            'order.orderServices',
            'order.priceList',
            'complaint',
            'services.priceLists',
            'histories.user' 
        ]);

        if ($user->role === UserRoles::DRIVER->value) {
            $query->whereHas(
                'order',
                fn($orderQuery) => $orderQuery->where('assigned_driver_id', $user->driver->id)
            );
        }

        return $query->when(request('qr_code'), function ($queryOrderCarpet) {
            $queryOrderCarpet->where('qr_code', 'like', '%' . request('qr_code') . '%');
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

            if (
                isset($data['height']) && isset($data['width']) &&
                is_numeric($data['height']) && is_numeric($data['width'])
            ) {
                $data['total_area'] = round((float)$data['height'] * (float)$data['width'], 2);
            }

            $orderCarpet = OrderCarpet::create($data);

            // Log carpet creation to order history
            OrderObserver::logCarpetAdded($order, $orderCarpet->id);

            // Sync services and log them
            $this->syncServicesWithHistory($orderCarpet, $order, $data['services'] ?? []);

            if (! $order->is_complaint) {
                $this->recalculateOrderTotals($order);
            }

            return $orderCarpet->load(
                'order.client',
                'order.driver.user',
                'order.priceList',
                'complaint',
                'services.priceLists',
                'histories.user'
            );
        });
    }

    public function updateOrderCarpet(OrderCarpet $orderCarpet, array $data)
    {
        return DB::transaction(function () use ($orderCarpet, $data) {
            $order = $orderCarpet->order;
            $originalServices = $orderCarpet->services->pluck('id')->toArray();

            // Calculate total_area automatically if height and width are provided
            if (
                isset($data['height']) && isset($data['width']) &&
                is_numeric($data['height']) && is_numeric($data['width'])
            ) {
                $data['total_area'] = round((float)$data['height'] * (float)$data['width'], 2);

                // If this is the first time being measured, set measured_at
                if (!$orderCarpet->measured_at) {
                    $data['measured_at'] = now();
                }
            }

            // Store original data for history tracking
            $original = $orderCarpet->toArray();

            $orderCarpet->update($data);

            // Sync services with history tracking
            $this->syncServicesWithHistory($orderCarpet, $order, $data['services'] ?? [], $originalServices);

            if (! $order->is_complaint) {
                $this->recalculateOrderTotals($order);
            }

            return $orderCarpet->load(
                'order.client',
                'orderCarpetPhotos',
                'order.driver.user',
                'order.priceList',
                'services.priceLists',
                'histories.user'
            );
        });
    }

    public function deleteCarpet(OrderCarpet $orderCarpet)
    {
        DB::transaction(function () use ($orderCarpet) {
            $order = $orderCarpet->order;
            $carpetId = $orderCarpet->id;

            $orderCarpet->delete();

            // Log carpet removal to order history
            OrderObserver::logCarpetRemoved($order, $carpetId);

            $order->load('orderCarpets.services');
            $this->recalculateOrderTotals($order);
        });

        return true;
    }

    /**
     * Add a single service to carpet with history logging
     */
    public function addServiceToCarpet(OrderCarpet $orderCarpet, int $serviceId): void
    {
        DB::transaction(function () use ($orderCarpet, $serviceId) {
            $service = Service::findOrFail($serviceId);
            $totalPrice = $this->calculateServiceTotal($serviceId, $orderCarpet->order, $orderCarpet);

            // Check if service is not already attached
            if (!$orderCarpet->services()->where('service_id', $serviceId)->exists()) {
                $orderCarpet->services()->attach($serviceId, ['total_price' => $totalPrice]);

                // Log service addition
                OrderCarpetObserver::logServiceAdded($orderCarpet, $serviceId, $service->name);

                // Recalculate order totals if not a complaint
                if (!$orderCarpet->order->is_complaint) {
                    $this->recalculateOrderTotals($orderCarpet->order);
                }
            }
        });
    }

    /**
     * Remove a service from carpet with history logging
     */
    public function removeServiceFromCarpet(OrderCarpet $orderCarpet, int $serviceId): void
    {
        DB::transaction(function () use ($orderCarpet, $serviceId) {
            $service = Service::findOrFail($serviceId);

            // Check if service is attached
            if ($orderCarpet->services()->where('service_id', $serviceId)->exists()) {
                $orderCarpet->services()->detach($serviceId);

                // Log service removal
                OrderCarpetObserver::logServiceRemoved($orderCarpet, $serviceId, $service->name);

                // Recalculate order totals if not a complaint
                if (!$orderCarpet->order->is_complaint) {
                    $this->recalculateOrderTotals($orderCarpet->order);
                }
            }
        });
    }

    /**
     * Update carpet status with automatic history logging
     */
    public function updateCarpetStatus(OrderCarpet $orderCarpet, string $status, ?string $notes = null): OrderCarpet
    {
        $orderCarpet->update([
            'status' => $status,
            'remarks' => $notes ?? $orderCarpet->remarks,
        ]);

        return $orderCarpet->fresh();
    }

    /**
     * Update carpet measurements with history logging
     */
    public function updateCarpetMeasurements(OrderCarpet $orderCarpet, float $height, float $width, ?string $remarks = null): OrderCarpet
    {
        $totalArea = round($height * $width, 2);

        $orderCarpet->update([
            'height' => $height,
            'width' => $width,
            'total_area' => $totalArea,
            'measured_at' => now(),
            'remarks' => $remarks ?? $orderCarpet->remarks,
        ]);

        // Recalculate service prices for area-based services
        $this->recalculateAreaBasedServices($orderCarpet);

        if (!$orderCarpet->order->is_complaint) {
            $this->recalculateOrderTotals($orderCarpet->order);
        }

        return $orderCarpet->fresh();
    }

    /**
     * Bulk update carpet statuses
     */
    public function bulkUpdateStatus(array $carpetIds, string $status): int
    {
        $updated = 0;

        DB::transaction(function () use ($carpetIds, $status, &$updated) {
            foreach ($carpetIds as $carpetId) {
                $carpet = OrderCarpet::find($carpetId);
                if ($carpet && $carpet->status !== $status) {
                    $carpet->update(['status' => $status]);
                    $updated++;
                }
            }
        });

        return $updated;
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

    /**
     * Sync services with history tracking
     */
    private function syncServicesWithHistory(OrderCarpet $orderCarpet, Order $order, array $serviceIds, array $originalServices = [])
    {
        if (empty($serviceIds)) {
            // If no services provided, remove all existing services
            if (!empty($originalServices)) {
                foreach ($originalServices as $serviceId) {
                    $service = Service::find($serviceId);
                    if ($service) {
                        OrderCarpetObserver::logServiceRemoved($orderCarpet, $serviceId, $service->name);
                    }
                }
            }
            $orderCarpet->services()->sync([]);
            return;
        }

        $servicesData = array_reduce($serviceIds, function ($result, $serviceId) use ($order, $orderCarpet) {
            $result[$serviceId] = [
                'total_price' => $this->calculateServiceTotal($serviceId, $order, $orderCarpet),
            ];
            return $result;
        }, []);

        // Track added services
        $addedServices = array_diff($serviceIds, $originalServices);
        foreach ($addedServices as $serviceId) {
            $service = Service::find($serviceId);
            if ($service) {
                OrderCarpetObserver::logServiceAdded($orderCarpet, $serviceId, $service->name);
            }
        }

        // Track removed services
        $removedServices = array_diff($originalServices, $serviceIds);
        foreach ($removedServices as $serviceId) {
            $service = Service::find($serviceId);
            if ($service) {
                OrderCarpetObserver::logServiceRemoved($orderCarpet, $serviceId, $service->name);
            }
        }

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

    /**
     * Recalculate prices for area-based services when measurements change
     */
    private function recalculateAreaBasedServices(OrderCarpet $orderCarpet): void
    {
        $order = $orderCarpet->order;
        $servicesData = [];

        foreach ($orderCarpet->services as $service) {
            $servicesData[$service->id] = [
                'total_price' => $this->calculateServiceTotal($service->id, $order, $orderCarpet),
            ];
        }

        if (!empty($servicesData)) {
            $orderCarpet->services()->sync($servicesData);
        }
    }

    /**
     * Get carpet statistics for dashboard
     */
    public function getCarpetStatistics(): array
    {
        $user = Auth::user();
        $query = OrderCarpet::query();

        if ($user->role === UserRoles::DRIVER->value) {
            $query->whereHas('order', fn($q) => $q->where('assigned_driver_id', $user->driver->id));
        }

        return [
            'total' => $query->count(),
            'pending' => $query->where('status', 'pending')->count(),
            'in_progress' => $query->whereIn('status', ['picked_up', 'at_laundry', 'measured'])->count(),
            'completed' => $query->where('status', 'completed')->count(),
            'delivered' => $query->where('status', 'delivered')->count(),
            'complaints' => $query->where('status', 'complaint')->count(),
        ];
    }

    /**
     * Search carpets by QR code or reference
     */
    public function searchByQrCode(string $qrCode): ?OrderCarpet
    {
        return OrderCarpet::with([
            'order.client',
            'order.driver.user',
            'orderCarpetPhotos',
            'services',
            'histories.user'
        ])->where('qr_code', 'like', "%{$qrCode}%")->first();
    }
}
