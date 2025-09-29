<?php

namespace App\Livewire\Clients;

use App\ActionService\ClientPdfReportService;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderCarpet;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class ViewPage extends Component
{
    use WithPagination;

    public Client $client;
    public $activeTab = 'overview';
    public $ordersPerPage = 10;
    public $carpetsPerPage = 12;
    public $selectedOrderId = null;
    public $dateFrom = '';
    public $dateTo = '';
    public $showDateFilter = false;
    public $geocodingInProgress = false;

    protected $queryString = [
        'activeTab' => ['except' => 'overview'],
        'selectedOrderId' => ['except' => null],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'showDateFilter' => ['except' => false],
    ];

    public function mount(Client $client)
    {
        $this->client = $client;
        $this->authorize('view', $client);
    }

    public function manualGeocode()
    {
        $this->geocodingInProgress = true;
        
        if ($this->client->forceGeocode()) {
            $this->client->refresh();
            session()->flash('success', __('Address successfully geocoded!'));
        } else {
            session()->flash('error', __('Unable to geocode the address. Please check if the address is correct.'));
        }
        
        $this->geocodingInProgress = false;
        $this->dispatch('addressGeocoded');
    }

    public function generatePdfReport()
    {
        $reportService = new ClientPdfReportService();
        $dateFrom = $this->dateFrom ? Carbon::parse($this->dateFrom)->startOfDay() : null;
        $dateTo = $this->dateTo ? Carbon::parse($this->dateTo)->endOfDay() : null;
        
        $filename = $reportService->generateClientReport($this->client, $dateFrom, $dateTo);

        return response()->download(storage_path('app/public/' . $filename))
            ->deleteFileAfterSend();
    }

    public function toggleDateFilter()
    {
        $this->showDateFilter = !$this->showDateFilter;
        if (!$this->showDateFilter) {
            $this->clearDateFilter();
        }
    }

    public function clearDateFilter()
    {
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

    public function applyDateFilter()
    {
        $this->resetPage();
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function selectOrder($orderId)
    {
        $this->selectedOrderId = $this->selectedOrderId === $orderId ? null : $orderId;
    }

    public function getOrdersProperty()
    {
        $query = $this->client->orders()
            ->with(['driver.user', 'priceList', 'orderCarpets']);
        
        if ($this->dateFrom) {
            $query->where('created_at', '>=', Carbon::parse($this->dateFrom)->startOfDay());
        }
        
        if ($this->dateTo) {
            $query->where('created_at', '<=', Carbon::parse($this->dateTo)->endOfDay());
        }
        
        return $query->latest('created_at')
            ->paginate($this->ordersPerPage, ['*'], 'orders-page');
    }

    public function getCarpetsProperty()
    {
        $query = OrderCarpet::whereHas('order', function ($query) {
            $subQuery = $query->where('client_id', $this->client->id);
            
            if ($this->dateFrom) {
                $subQuery->where('created_at', '>=', Carbon::parse($this->dateFrom)->startOfDay());
            }
            
            if ($this->dateTo) {
                $subQuery->where('created_at', '<=', Carbon::parse($this->dateTo)->endOfDay());
            }
        })->with(['order', 'services']);
        
        return $query->latest('created_at')
            ->paginate($this->carpetsPerPage, ['*'], 'carpets-page');
    }

    public function getStatsProperty()
    {
        $ordersQuery = $this->client->orders();
        $carpetsQuery = OrderCarpet::whereHas('order', function ($query) {
            $query->where('client_id', $this->client->id);
        });

        if ($this->dateFrom) {
            $ordersQuery->where('created_at', '>=', Carbon::parse($this->dateFrom)->startOfDay());
            $carpetsQuery->whereHas('order', function ($query) {
                $query->where('client_id', $this->client->id)
                     ->where('created_at', '>=', Carbon::parse($this->dateFrom)->startOfDay());
            });
        }
        
        if ($this->dateTo) {
            $ordersQuery->where('created_at', '<=', Carbon::parse($this->dateTo)->endOfDay());
            $carpetsQuery->whereHas('order', function ($query) {
                $query->where('client_id', $this->client->id)
                     ->where('created_at', '<=', Carbon::parse($this->dateTo)->endOfDay());
            });
        }

        return [
            'total_orders' => $ordersQuery->count(),
            'completed_orders' => (clone $ordersQuery)->where('status', 'completed')->count(),
            'total_carpets' => $carpetsQuery->count(),
            'total_spent' => $ordersQuery->sum('total_amount') ?? 0,
        ];
    }

    public function render()
    {
        return view('livewire.clients.view-page', [
            'orders' => $this->orders,
            'carpets' => $this->carpets,
            'stats' => $this->stats,
        ]);
    }
}