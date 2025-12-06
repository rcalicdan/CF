<?php

namespace App\Livewire\Orders;

use App\ActionService\OrderService;
use App\Models\Client;
use App\Models\Driver;
use App\Models\Order;
use App\Models\PriceList;
use Illuminate\Support\Facades\Log;
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

    public $showCreateClientModal = false;
    public $newClient = [
        'first_name' => '',
        'last_name' => '',
        'email' => '',
        'phone_number' => '',
        'street_name' => '',
        'street_number' => '',
        'postal_code' => '',
        'city' => '',
        'remarks' => '',
    ];

    public function openCreateClientModal()
    {
        $this->showCreateClientModal = true;
        $this->resetNewClientForm();
    }

    public function closeCreateClientModal()
    {
        $this->showCreateClientModal = false;
        $this->resetNewClientForm();
    }

    public function resetNewClientForm()
    {
        $this->newClient = [
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'phone_number' => '',
            'street_name' => '',
            'street_number' => '',
            'postal_code' => '',
            'city' => '',
            'remarks' => '',
        ];
        $this->resetValidation([
            'newClient.first_name',
            'newClient.last_name',
            'newClient.email',
            'newClient.phone_number',
            'newClient.street_name',
            'newClient.street_number',
            'newClient.postal_code',
            'newClient.city',
        ]);
    }

    public function createClient()
    {
        $this->validate([
            'newClient.first_name' => 'required|string|max:255',
            'newClient.last_name' => 'required|string|max:255',
            'newClient.email' => 'nullable|email|max:255',
            'newClient.phone_number' => 'nullable|string|max:20',
            'newClient.street_name' => 'required|string|max:255',
            'newClient.street_number' => 'required|string|max:20',
            'newClient.postal_code' => 'required|string|max:10',
            'newClient.city' => 'required|string|max:255',
            'newClient.remarks' => 'nullable|string',
        ], [
            'newClient.first_name.required' => 'Podaj imię klienta.',
            'newClient.last_name.required' => 'Podaj nazwisko klienta.',
            'newClient.email.email' => 'Podaj poprawny adres email.',
            'newClient.street_name.required' => 'Podaj nazwę ulicy.',
            'newClient.street_number.required' => 'Podaj numer domu.',
            'newClient.postal_code.required' => 'Podaj kod pocztowy.',
            'newClient.city.required' => 'Podaj nazwę miasta.',
        ]);

        try {
            $client = Client::create($this->newClient);

            $this->showDriversDropdown = false;
            $this->showPriceListsDropdown = false;
            $this->showClientsDropdown = false;

            $this->selectClient($client->id, $client->full_name);

            $this->closeCreateClientModal();

            session()->flash('client-created', 'Klient został pomyślnie dodany!');
        } catch (\Exception $e) {
            $this->addError('newClient', 'Nie udało się dodać klienta. Spróbuj ponownie.');
            Log::error('Client creation failed', ['error' => $e->getMessage()]);
        }
    }

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

    public function messages()
    {
        return [
            'client_id.required' => 'Wybierz klienta z listy.',
            'client_id.exists' => 'Wybrany klient nie istnieje w systemie.',
            'assigned_driver_id.exists' => 'Wybrany kierowca nie istnieje w systemie.',
            'schedule_date.date' => 'Podaj poprawną datę realizacji.',
            'schedule_date.after_or_equal' => 'Data realizacji nie może być wcześniejsza niż dzisiaj.',
            'schedule_date.date_format' => 'Format daty jest nieprawidłowy.',
            'price_list_id.required' => 'Wybierz cennik z listy.',
            'price_list_id.exists' => 'Wybrany cennik nie istnieje w systemie.',
            'status.required' => 'Wybierz status zamówienia.',
            'status.in' => 'Wybrany status jest nieprawidłowy.',
            'is_complaint.boolean' => 'Zaznacz czy to jest reklamacja.',
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
                $searchTerm = preg_replace('/\s+/', ' ', trim($this->clientSearch)); // normalize spaces

                $q->where('first_name', 'ILIKE', '%' . $searchTerm . '%')
                    ->orWhere('last_name', 'ILIKE', '%' . $searchTerm . '%')
                    ->orWhereRaw('REGEXP_REPLACE(first_name || \' \' || last_name, \'\s+\', \' \', \'g\') ILIKE ?', ['%' . $searchTerm . '%']);
            });
        }

        return $query->limit(10)->get();
    }

    public function getFilteredDrivers()
    {
        $query = Driver::with('user:id,first_name,last_name')
            ->active();

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
            session()->flash('success', 'Zamówienie zostało pomyślnie utworzone!');
            return $this->redirect(route('orders.show', [$result["order"]->id]), true);
        } catch (\Exception $e) {
            session()->flash('error', 'Wystąpił błąd podczas tworzenia zamówienia. Proszę spróbować ponownie.');
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
