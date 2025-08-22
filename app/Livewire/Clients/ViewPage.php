<?php

namespace App\Livewire\Clients;

use App\ActionService\ClientPdfReportService;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderCarpet;
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

    protected $queryString = [
        'activeTab' => ['except' => 'overview'],
        'selectedOrderId' => ['except' => null]
    ];

    public function mount(Client $client)
    {
        $this->client = $client;
        $this->authorize('view', $client);
    }

    public function generatePdfReport()
    {
        $reportService = new ClientPdfReportService();
        $filename = $reportService->generateClientReport($this->client);

        return response()->download(storage_path('app/public/' . $filename))
            ->deleteFileAfterSend();
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
        return $this->client->orders()
            ->with(['driver.user', 'priceList', 'orderCarpets'])
            ->latest('created_at')
            ->paginate($this->ordersPerPage, ['*'], 'orders-page');
    }

    public function getCarpetsProperty()
    {
        return OrderCarpet::whereHas('order', function ($query) {
            $query->where('client_id', $this->client->id);
        })
            ->with(['order', 'services'])
            ->latest('created_at')
            ->paginate($this->carpetsPerPage, ['*'], 'carpets-page');
    }

    public function getStatsProperty()
    {
        return [
            'total_orders' => $this->client->orders()->count(),
            'completed_orders' => $this->client->orders()->where('status', 'completed')->count(),
            'total_carpets' => OrderCarpet::whereHas('order', function ($query) {
                $query->where('client_id', $this->client->id);
            })->count(),
            'total_spent' => $this->client->orders()->sum('total_amount'),
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
