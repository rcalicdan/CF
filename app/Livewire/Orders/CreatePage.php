<?php

namespace App\Livewire\Orders;

use App\ActionService\OrderService;
use App\Models\Client;
use App\Models\Driver;
use App\Models\Order;
use App\Models\PriceList;
use Livewire\Component;

class CreatePage extends Component
{
    public $client_id = '';
    public $assigned_driver_id = '';
    public $schedule_date = '';
    public $price_list_id = '';
    public $is_complaint = false;
    public $clientSearch = '';
    public $driverSearch = '';
    public $priceListSearch = '';
    public $showClientsDropdown = false;
    public $showDriversDropdown = false;
    public $showPriceListsDropdown = false;
    public $selectedClient = null;
    public $selectedDriver = null;
    public $selectedPriceList = null;

    protected OrderService $orderService;

    public function boot(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function rules()
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'assigned_driver_id' => 'nullable|exists:drivers,id',
            'schedule_date' => 'nullable|date|after_or_equal:today',
            'price_list_id' => 'required|exists:price_lists,id',
            'is_complaint' => 'boolean',
        ];
    }

    public function validationAttributes()
    {
        return [
            'client_id' => 'client',
            'assigned_driver_id' => 'assigned driver',
            'schedule_date' => 'schedule date',
            'price_list_id' => 'price list',
            'is_complaint' => 'complaint status',
        ];
    }

    public function updatedClientSearch()
    {
        $this->showClientsDropdown = !empty($this->clientSearch);
        if (empty($this->clientSearch)) {
            $this->client_id = '';
            $this->selectedClient = null;
        }
    }

    public function selectClient($clientId, $clientName)
    {
        $this->client_id = $clientId;
        $this->clientSearch = $clientName;
        $this->showClientsDropdown = false;
        $this->selectedClient = Client::find($clientId);
    }

    public function showAllClients()
    {
        $this->showClientsDropdown = true;
        $this->clientSearch = '';
    }

    public function hideClientsDropdown()
    {
        $this->showClientsDropdown = false;
        if (!$this->client_id) {
            $this->clientSearch = '';
        }
    }

    public function updatedDriverSearch()
    {
        $this->showDriversDropdown = !empty($this->driverSearch);
        if (empty($this->driverSearch)) {
            $this->assigned_driver_id = '';
            $this->selectedDriver = null;
        }
    }

    public function selectDriver($driverId, $driverName)
    {
        $this->assigned_driver_id = $driverId;
        $this->driverSearch = $driverName;
        $this->showDriversDropdown = false;
        $this->selectedDriver = Driver::with('user')->find($driverId);
    }

    public function clearDriver()
    {
        $this->assigned_driver_id = '';
        $this->driverSearch = '';
        $this->selectedDriver = null;
    }

    public function showAllDrivers()
    {
        $this->showDriversDropdown = true;
        $this->driverSearch = '';
    }

    public function hideDriversDropdown()
    {
        $this->showDriversDropdown = false;
        if (!$this->assigned_driver_id) {
            $this->driverSearch = '';
        }
    }

    // Price List search methods
    public function updatedPriceListSearch()
    {
        $this->showPriceListsDropdown = !empty($this->priceListSearch);
        if (empty($this->priceListSearch)) {
            $this->price_list_id = '';
            $this->selectedPriceList = null;
        }
    }

    public function selectPriceList($priceListId, $priceListName)
    {
        $this->price_list_id = $priceListId;
        $this->priceListSearch = $priceListName;
        $this->showPriceListsDropdown = false;
        $this->selectedPriceList = PriceList::find($priceListId);
    }

    public function showAllPriceLists()
    {
        $this->showPriceListsDropdown = true;
        $this->priceListSearch = '';
    }

    public function hidePriceListsDropdown()
    {
        $this->showPriceListsDropdown = false;
        if (!$this->price_list_id) {
            $this->priceListSearch = '';
        }
    }

    public function getFilteredClients()
    {
        $query = Client::select(['id', 'first_name', 'last_name']);

        if (!empty($this->clientSearch)) {
            $query->where(function ($q) {
                $q->where('first_name', 'like', '%' . $this->clientSearch . '%')
                    ->orWhere('last_name', 'like', '%' . $this->clientSearch . '%')
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $this->clientSearch . '%']);
            });
        }

        return $query->limit(10)->get();
    }

    public function getFilteredDrivers()
    {
        $query = Driver::with('user:id,first_name,last_name');

        if (!empty($this->driverSearch)) {
            $query->whereHas('user', function ($q) {
                $q->where('first_name', 'like', '%' . $this->driverSearch . '%')
                    ->orWhere('last_name', 'like', '%' . $this->driverSearch . '%')
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $this->driverSearch . '%']);
            });
        }

        return $query->limit(10)->get();
    }

    public function getFilteredPriceLists()
    {
        $query = PriceList::select(['id', 'name', 'location_postal_code']);

        if (!empty($this->priceListSearch)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->priceListSearch . '%')
                    ->orWhere('location_postal_code', 'like', '%' . $this->priceListSearch . '%');
            });
        }

        return $query->limit(10)->get();
    }

    public function save()
    {
        $this->authorize('create', Order::class);
        $this->validate();

        $data = [
            'client_id' => $this->client_id,
            'assigned_driver_id' => $this->assigned_driver_id ?: null,
            'schedule_date' => $this->schedule_date ?: null,
            'price_list_id' => $this->price_list_id,
            'is_complaint' => $this->is_complaint,
        ];

        try {
            $result = $this->orderService->createOrder($data);
            session()->flash('success', 'Order created successfully!');
            return $this->redirect(route('orders.show', [$result["order"]->id]), true);
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while creating the order. Please try again.');
        }
    }

    public function render()
    {
        $this->authorize('create', Order::class);
        return view('livewire.orders.create-page', [
            'filteredClients' => $this->getFilteredClients(),
            'filteredDrivers' => $this->getFilteredDrivers(),
            'filteredPriceLists' => $this->getFilteredPriceLists(),
        ]);
    }
}
