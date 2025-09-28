<?php

namespace App\Livewire\Orders\Carpets;

use App\ActionService\OrderCarpetService;
use App\Enums\OrderCarpetStatus;
use App\Models\Order;
use App\Models\OrderCarpet;
use App\Models\Service;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class CarpetCreatePage extends Component
{
    use AuthorizesRequests;

    public Order $order;
    public $height = '';
    public $width = '';
    public $status = 'pending';
    public $remarks = '';
    public $services = [];
    public $selectedServices = [];
    public $serviceSearch = '';
    public $showServicesDropdown = false;

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

    public function mount(Order $order)
    {
        $this->order = $order;
        $this->authorize('view', $order);
        $this->services = Service::orderBy('name')->get();
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
        if ($service->is_area_based) {
            return $this->totalArea > 0 ? $service->base_price * $this->totalArea : 0;
        }
        return $service->base_price;
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
        $this->authorize('createCarpet', $this->order);
        $this->validate();

        try {
            $data = [
                'order_id' => $this->order->id,
                'height' => $this->height ?: null,
                'width' => $this->width ?: null,
                'status' => $this->status,
                'remarks' => $this->remarks ?: null,
                'services' => $this->selectedServices,
            ];

            if ($this->height && $this->width) {
                $data['measured_at'] = now();
            }

            $orderCarpetService->storeOrderCarpet($data);

            session()->flash('success', 'Dywan został pomyślnie dodany do zamówienia.');

            return $this->redirect(route('orders.show', $this->order), true);
        } catch (\Exception $e) {
            session()->flash('error', 'Nie udało się dodać dywanu: ' . $e->getMessage());
        }
    }

    public function cancel()
    {
        return redirect()->route('orders.show', $this->order);
    }

    public function render()
    {
        $this->authorize('createCarpet', $this->order);
        $statusOptions = OrderCarpetStatus::options();

        return view('livewire.orders.carpets.create-page', [
            'statusOptions' => $statusOptions,
        ]);
    }
}