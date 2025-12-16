<?php

namespace App\Livewire\Orders\Carpets;

use App\ActionService\OrderCarpetService;
use App\Enums\OrderCarpetStatus;
use App\Models\Order;
use App\Models\OrderCarpet;
use App\Models\Service;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class CarpetUpdatePage extends Component
{
    use AuthorizesRequests;

    public Order $order;
    public OrderCarpet $carpet;
    public $height = '';
    public $width = '';
    public $status = '';
    public $remarks = '';
    public $services = [];
    public $selectedServices = [];
    public $serviceSearch = '';
    public $showServicesDropdown = false;
    public $servicePrices = [];

    protected $rules = [
        'height' => 'nullable|numeric|min:0',
        'width' => 'nullable|numeric|min:0',
        'status' => 'required|string|max:255',
        'remarks' => 'nullable|string',
        'selectedServices' => 'array',
        'selectedServices.*' => 'exists:services,id'
    ];

    protected $messages = [
        'selectedServices.*.exists' => 'One or more selected services are invalid.',
    ];

    public function mount(Order $order, OrderCarpet $carpet)
    {
        $this->carpet = $carpet->load('order.priceList', 'order.client', 'services');
        $this->order = $this->carpet->order; 

        $this->authorize('view', $this->order);

        $this->height = $carpet->height;
        $this->width = $carpet->width;
        $this->status = $carpet->status;
        $this->remarks = $carpet->remarks;
        $this->selectedServices = $carpet->services->pluck('id')->toArray();

        if (!$this->order->price_list_id) {
            \Log::error('Order has no price_list_id!', ['order_id' => $this->order->id]);
            session()->flash('error', 'This order has no price list assigned.');
            return;
        }

        $this->services = Service::with(['priceLists' => function ($query) {
            $query->where('price_list_id', $this->order->price_list_id);
        }])->orderBy('name')->get();

        $this->loadServicePrices();
    }

    private function loadServicePrices()
    {
        foreach ($this->services as $service) {
            $priceListPrice = $service->priceLists->first();
            $this->servicePrices[$service->id] = $priceListPrice ? $priceListPrice->pivot->price : $service->base_price;
        }
    }

    public function getServiceEffectivePrice($serviceId)
    {
        return $this->servicePrices[$serviceId] ?? 0;
    }

    public function updatedHeight()
    {
        // Trigger price recalculation
    }

    public function updatedWidth()
    {
        // Trigger price recalculation
    }

    public function updatedServiceSearch()
    {
        $this->showServicesDropdown = !empty($this->serviceSearch);
    }

    public function toggleService($serviceId)
    {
        if (in_array($serviceId, $this->selectedServices)) {
            $this->selectedServices = array_filter($this->selectedServices, fn($id) => $id != $serviceId);
        } else {
            $this->selectedServices[] = $serviceId;
        }
    }

    public function removeService($serviceId)
    {
        $this->selectedServices = array_filter($this->selectedServices, fn($id) => $id != $serviceId);
    }

    public function showAllServices()
    {
        $this->showServicesDropdown = true;
        $this->serviceSearch = '';
    }

    public function hideServicesDropdown()
    {
        $this->showServicesDropdown = false;
    }

    public function getFilteredServicesProperty()
    {
        if (empty($this->serviceSearch)) {
            return $this->services;
        }

        return $this->services->filter(function ($service) {
            return str_contains(strtolower($service->name), strtolower($this->serviceSearch));
        });
    }

    public function getSelectedServicesDataProperty()
    {
        return $this->services->whereIn('id', $this->selectedServices);
    }

    public function getTotalAreaProperty()
    {
        return $this->height && $this->width ? (float)$this->height * (float)$this->width : 0;
    }

    public function calculateServicePrice($service)
    {
        $effectivePrice = $this->getServiceEffectivePrice($service->id);

        if ($service->is_area_based) {
            return $this->totalArea > 0 ? $effectivePrice * $this->totalArea : 0;
        }
        return $effectivePrice;
    }

    public function getTotalPriceProperty()
    {
        $total = 0;
        foreach ($this->selectedServicesData as $service) {
            $total += $this->calculateServicePrice($service);
        }
        return $total;
    }

    public function save(OrderCarpetService $orderCarpetService)
    {
        $this->authorize('update', $this->carpet);
        $this->validate();

        try {
            $data = [
                'height' => $this->height ?: null,
                'width' => $this->width ?: null,
                'status' => $this->status,
                'remarks' => $this->remarks ?: null,
                'services' => $this->selectedServices,
            ];

            if ($this->height && $this->width && !$this->carpet->measured_at) {
                $data['measured_at'] = now();
            } elseif ((!$this->height || !$this->width) && $this->carpet->measured_at) {
                $data['measured_at'] = null;
            }

            $orderCarpetService->updateOrderCarpet($this->carpet, $data);

            session()->flash('success', 'Dywan został pomyślnie zaktualizowany.');

            return $this->redirect(route('orders.show', $this->carpet->order->id), true);
        } catch (\Exception $e) {
            session()->flash('error', 'Nie udało się zaktualizować dywanu: ' . $e->getMessage());
        }
    }

    public function cancel()
    {
        return $this->redirect(route('orders.show', $this->carpet->order->id), true);
    }

    public function render()
    {
        $this->authorize('update', $this->carpet);
        $statusOptions = OrderCarpetStatus::options();

        return view('livewire.orders.carpets.carpet-update-page', [
            'statusOptions' => $statusOptions,
        ]);
    }
}
