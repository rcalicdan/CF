<?php

namespace App\Livewire\PriceLists;

use App\ActionService\PriceListService;
use App\Models\PriceList;
use App\Models\Service;
use Livewire\Component;

class CreatePage extends Component
{
    public $name = '';
    public $location_postal_code = '';
    public $serviceSearch = '';
    public $selectedServiceId = null;
    public $newServicePrice = '';
    public $servicePrices = [];
    public $availableServices = [];
    public $showServiceDropdown = false;
    public $service_id = '';
    public $price = '';
    public $showPreview = false;
    public $bulkAdjustmentType = 'percentage';
    public $bulkAdjustmentValue = '';
    public $directServiceSearch = '';
    public $directAvailableServices = [];
    public $showDirectServiceDropdown = false;

    protected PriceListService $priceListService;

    public function boot(PriceListService $priceListService)
    {
        $this->priceListService = $priceListService;
    }

    public function mount()
    {
        $this->loadAvailableServices();
        $this->loadDirectAvailableServices();
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'location_postal_code' => 'required|string|max:20',
            'servicePrices.*.service_id' => 'required|exists:services,id',
            'servicePrices.*.price' => 'required|numeric|min:0',
        ];
    }

    public function validationAttributes()
    {
        return [
            'location_postal_code' => 'postal code',
        ];
    }

    public function updatedServiceSearch()
    {
        $this->loadAvailableServices();
        $this->showServiceDropdown = true;
    }

    public function updatedDirectServiceSearch()
    {
        $this->loadDirectAvailableServices();
        $this->showDirectServiceDropdown = true;
        $this->service_id = '';
    }

    public function loadAvailableServices()
    {
        $existingServiceIds = collect($this->servicePrices)->pluck('service_id')->toArray();

        $query = Service::whereNotIn('id', $existingServiceIds);

        if ($this->serviceSearch) {
            $query->where('name', 'like', '%' . $this->serviceSearch . '%');
        }

        $this->availableServices = $query->take(10)->get();
    }

    public function loadDirectAvailableServices()
    {
        $existingServiceIds = collect($this->servicePrices)->pluck('service_id')->toArray();

        $query = Service::whereNotIn('id', $existingServiceIds);

        if ($this->directServiceSearch) {
            $query->where('name', 'like', '%' . $this->directServiceSearch . '%');
        }

        $this->directAvailableServices = $query->orderBy('name')->take(10)->get();
    }

    public function selectDirectService($serviceId)
    {
        $service = Service::find($serviceId);
        if ($service) {
            $this->service_id = $serviceId;
            $this->directServiceSearch = $service->name;
            $this->price = number_format($service->base_price, 2, '.', '');
            $this->showDirectServiceDropdown = false;
        }
    }

    public function selectService($serviceId)
    {
        $service = Service::find($serviceId);
        if ($service) {
            $this->selectedServiceId = $serviceId;
            $this->serviceSearch = $service->name;
            $this->newServicePrice = number_format($service->base_price, 2, '.', '');
            $this->showServiceDropdown = false;
        }
    }

    public function addServiceFromDropdown()
    {
        $this->validate([
            'service_id' => 'required|exists:services,id',
            'price' => 'required|numeric|min:0|max:999999.99',
        ]);

        $service = Service::find($this->service_id);
        if (!$service) {
            return;
        }

        $existingIndex = collect($this->servicePrices)->search(function ($item) {
            return $item['service_id'] == $this->service_id;
        });

        if ($existingIndex !== false) {
            session()->flash('error', 'Usługa została już dodana do tego cennika.');
            return;
        }

        $this->servicePrices[] = [
            'service_id' => $service->id,
            'name' => $service->name,
            'base_price' => $service->base_price,
            'price' => (float) $this->price,
        ];

        $this->resetDirectServiceForm();
        $this->loadAvailableServices();
        $this->loadDirectAvailableServices();
        session()->flash('success', 'Usługa została pomyślnie dodana!');
    }

    public function addServicePrice()
    {
        if (!$this->selectedServiceId || !$this->newServicePrice) {
            return;
        }

        $service = Service::find($this->selectedServiceId);
        if (!$service) {
            return;
        }

        $existingIndex = collect($this->servicePrices)->search(function ($item) {
            return $item['service_id'] == $this->selectedServiceId;
        });

        if ($existingIndex !== false) {
            session()->flash('error', 'Usługa została już dodana do tego cennika.');
            return;
        }

        $this->servicePrices[] = [
            'service_id' => $service->id,
            'name' => $service->name,
            'base_price' => $service->base_price,
            'price' => (float) $this->newServicePrice,
        ];

        $this->resetServiceForm();
        $this->loadAvailableServices();
        $this->loadDirectAvailableServices();
    }

    public function removeServicePrice($index)
    {
        unset($this->servicePrices[$index]);
        $this->servicePrices = array_values($this->servicePrices);
        $this->loadAvailableServices();
        $this->loadDirectAvailableServices();
    }

    public function togglePreview()
    {
        $this->showPreview = !$this->showPreview;
    }

    private function resetServiceForm()
    {
        $this->selectedServiceId = null;
        $this->serviceSearch = '';
        $this->newServicePrice = '';
        $this->showServiceDropdown = false;
    }

    private function resetDirectServiceForm()
    {
        $this->service_id = '';
        $this->price = '';
        $this->directServiceSearch = '';
        $this->showDirectServiceDropdown = false;
    }

    public function save()
    {
        $this->authorize('create', PriceList::class);
        $this->validate();

        $data = [
            'name' => $this->name,
            'location_postal_code' => $this->location_postal_code,
        ];

        try {
            $priceList = $this->priceListService->createPriceListWithServices($data, $this->servicePrices);

            session()->flash('success', 'Cennik został pomyślnie utworzony!');

            return redirect()->route('price-lists.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Wystąpił błąd podczas tworzenia cennika. Proszę spróbować ponownie.');
        }
    }

    public function render()
    {
        $this->authorize('create', PriceList::class);

        return view('livewire.price-lists.create-page');
    }

    public function applyBulkAdjustment()
    {
        $this->validate([
            'bulkAdjustmentType' => 'required|in:percentage,fixed',
            'bulkAdjustmentValue' => 'required|numeric',
        ]);

        foreach ($this->servicePrices as $index => $servicePrice) {
            if ($this->bulkAdjustmentType === 'percentage') {
                $adjustment = ($servicePrice['price'] * $this->bulkAdjustmentValue) / 100;
                $this->servicePrices[$index]['price'] = $servicePrice['price'] + $adjustment;
            } else {
                $this->servicePrices[$index]['price'] = $servicePrice['price'] + $this->bulkAdjustmentValue;
            }

            $this->servicePrices[$index]['price'] = max(0, $this->servicePrices[$index]['price']);
        }

        $this->bulkAdjustmentValue = '';
        session()->flash('success', 'Masowa korekta cen została pomyślnie zastosowana!');
    }
}
