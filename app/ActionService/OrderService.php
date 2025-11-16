<?php

namespace App\ActionService;

use App\Enums\UserRoles;
use App\Jobs\SendSmsJob;
use App\Models\Order;
use App\Models\Service;
use App\Models\ServicePriceList;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function getOrders()
    {
        $user = Auth::user();
        $query = Order::with([
            'priceList',
            'client',
            'driver.user',
            'orderCarpets.orderCarpetPhotos.user',
            'orderCarpets.complaint',
            'orderCarpets.services.priceLists',
            'orderDeliveryConfirmation',
            'orderPayment',
        ]);

        if ($user->role === UserRoles::DRIVER->value) {
            $query->where('assigned_driver_id', $user->driver->id);
        }

        $query->when(request('search'), function($q) {
                $searchTerm = '%' . request('search') . '%';
                return $q->where(function($query) use ($searchTerm) {
                    $query->where('id', 'like', $searchTerm)
                        ->orWhereHas('client', function($q) use ($searchTerm) {
                            $q->where('first_name', 'ilike', $searchTerm)
                                ->orWhere('last_name', 'ilike', $searchTerm)
                                ->orWhereRaw("CONCAT(first_name, ' ', last_name) ILIKE ?", [$searchTerm]);
                        })
                        ->orWhereHas('driver.user', function($q) use ($searchTerm) {
                            $q->where('first_name', 'ilike', $searchTerm)
                                ->orWhere('last_name', 'ilike', $searchTerm)
                                ->orWhereRaw("CONCAT(first_name, ' ', last_name) ILIKE ?", [$searchTerm]);
                        });
                });
            })
            ->when(request('order_id'), fn($q) => $q->where('id', request('order_id')))
            ->when(request('client_first_name'), fn($q) => $q->whereHas('client', fn($s) => $s->where('first_name', 'like', '%' . request('client_first_name') . '%')))
            ->when(request('client_last_name'), fn($q) => $q->whereHas('client', fn($s) => $s->where('last_name', 'like', '%' . request('client_last_name') . '%')))
            ->when(request('price_list_name'), fn($q) => $q->whereHas('priceList', fn($s) => $s->where('name', 'like', '%' . request('price_list_name') . '%')))
            ->when(request('order_carpet_status'), fn($q) => $q->whereHas('orderCarpets', fn($s) => $s->where('status', request('order_carpet_status'))))
            ->when(request('schedule_date'), fn($q) => $q->whereDate('schedule_date', request('schedule_date')))
            ->when(request('status'), fn($q) => $q->where('status', request('status')))
            ->when(request('created_at'), fn($q) => $q->whereDate('created_at', request('created_at')))
            ->when(request('driver_name'), fn($q) => $q->whereHas('driver.user', function ($s) {
                $term = '%' . str_replace(['%', '_'], ['\\%', '\\_'], request('driver_name')) . '%';
                $s->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$term]);
            }))
            ->when(request('driver_id'), fn($q) => $q->where('assigned_driver_id', request('driver_id')));

        return $query->paginate(30);
    }

    public function createOrder(array $data)
    {
        return DB::transaction(function () use ($data) {
            $order = Order::create([
                'client_id' => $data['client_id'],
                'assigned_driver_id' => $data['assigned_driver_id'] ?? null,
                'user_id' => Auth::user()->id,
                'schedule_date' => $data['schedule_date'] ?? null,
                'price_list_id' => $data['price_list_id'],
                'status' => 'pending',
                'total_amount' => 0,
                'is_complaint' => $data['is_complaint'] ?? false,
            ]);

            $order->load('client', 'driver.user', 'priceList', 'orderCarpets.complaint');

            SendSmsJob::dispatch(
                preg_replace('/[^\d]/', '', $order->client->phone_number),
                __('Your order has been created. We will send later the schedule date for delivery')
            )->afterCommit();

            return [
                'order' => $order,
                'summary' => $this->generateOrderSummary($order),
            ];
        });
    }

    public function updateOrder(Order $order, array $data)
    {
        return DB::transaction(function () use ($order, $data) {
            $originalValues = $this->getOriginalOrderValues($order);

            $this->updateOrderAttributes($order, $data);

            if ($this->shouldRecalculatePrices($order, $originalValues)) {
                $this->recalculateAndUpdatePrices($order);
            }

            $this->refreshOrderRelations($order);

            return [
                'order' => $order,
                'summary' => $this->generateOrderSummary($order),
            ];
        });
    }

    public function showOrder(Order $order)
    {
        return [
            'order' => $order->load([
                'client',
                'driver.user',
                'priceList',
                'orderCarpets.orderCarpetPhotos.user',
                'orderCarpets.complaint',
                'orderCarpets.services',
                'orderDeliveryConfirmation',
                'orderPayment',
            ]),
        ];
    }

    private function recalculateCarpetServicePrices(Order $order): void
    {
        foreach ($order->orderCarpets as $carpet) {
            foreach ($carpet->services as $service) {
                $price = $this->getServicePrice($service, $order->price_list_id, $service->base_price);

                $totalPrice = $order->is_complaint
                    ? 0
                    : ($service->is_area_based
                        ? $price * ($carpet->total_area ?? 1)
                        : $price);

                $carpet->services()->updateExistingPivot($service->id, [
                    'total_price' => $totalPrice,
                ]);
            }
        }
    }

    private function recalculateOrderTotalAmount(Order $order): void
    {
        $totalAmount = DB::table('order_carpets as oc')
            ->join('carpet_services as cs', 'oc.id', '=', 'cs.order_carpet_id')
            ->where('oc.order_id', $order->id)
            ->sum('cs.total_price') ?? 0;

        $order->update(['total_amount' => $totalAmount]);
        $order->refresh();
    }

    private function generateOrderSummary(Order $order): array
    {
        return [
            'order_id' => $order->id,
            'client_id' => $order->client_id,
            'client_name' => $order->client->full_name,
            'assigned_driver_id' => $order->assigned_driver_id,
            'driver_name' => $order->driver?->user->full_name,
            'price_list_id' => $order->price_list_id,
            'price_list_name' => $order->priceList->name,
            'status' => $order->status,
            'total_amount' => $order->total_amount,
        ];
    }

    private function getServicePrice(
        Service $service,
        int $priceListId,
        float $basePrice
    ): float {
        return ServicePriceList::where('service_id', $service->id)
            ->where('price_list_id', $priceListId)
            ->value('price') ?? $basePrice;
    }

    private function getOriginalOrderValues(Order $order): array
    {
        return [
            'price_list_id' => $order->price_list_id,
            'is_complaint' => $order->is_complaint,
        ];
    }

    private function updateOrderAttributes(Order $order, array $data): void
    {
        $order->update($this->getUpdatedAttributes($order, $data));
    }

    private function getUpdatedAttributes(Order $order, array $data): array
    {
        return [
            'client_id' => $data['client_id'] ?? $order->client_id,
            'assigned_driver_id' => $data['assigned_driver_id'] ?? $order->assigned_driver_id,
            'schedule_date' => $data['schedule_date'] ?? $order->schedule_date,
            'price_list_id' => $data['price_list_id'] ?? $order->price_list_id,
            'status' => $data['status'] ?? $order->status,
            'is_complaint' => $data['is_complaint'] ?? $order->is_complaint,
        ];
    }

    private function shouldRecalculatePrices(Order $order, array $originalValues): bool
    {
        return $order->price_list_id !== $originalValues['price_list_id']
            || $order->is_complaint !== $originalValues['is_complaint'];
    }

    private function recalculateAndUpdatePrices(Order $order): void
    {
        $this->recalculateCarpetServicePrices($order);
        $this->recalculateOrderTotalAmount($order);
    }

    private function refreshOrderRelations(Order $order): void
    {
        $order->load([
            'priceList',
            'client',
            'driver.user',
            'orderCarpets.services.priceLists',
            'orderCarpets.complaint',
        ]);
    }
}
